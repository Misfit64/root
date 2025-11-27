<div class="h-full flex flex-col bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 shadow z-10">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $tree->name }} - Visualizer</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Root: {{ $rootPerson->full_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="toggleWholeTree" wire:loading.attr="disabled" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="toggleWholeTree">
                        {{ $isWholeTree ? 'Show Focused View' : 'Show Whole Tree' }}
                    </span>
                    <span wire:loading wire:target="toggleWholeTree">
                        Loading...
                    </span>
                </button>
                @auth
                <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $rootPerson->id]) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Back to Profile
                </a>
                @endauth
            </div>
        </div>
    </div>

    <div id="tree-container" class="flex-grow overflow-hidden relative">
        {{-- D3 Graph will be rendered here --}}
        <button id="reset-zoom" class="absolute bottom-4 right-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 z-20">
            Reset Zoom
        </button>
    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const initialData = @json($this->graphData);
            const rootId = @json($rootPerson->id);
            
            const renderGraph = (data) => {
                const container = document.getElementById('tree-container');
                const width = container.clientWidth;
                const height = container.clientHeight;
                const spouseOffset = 100;

                // Clear previous SVG if any
                d3.select('#tree-container').selectAll('svg').remove();

                const svg = d3.select('#tree-container').append('svg')
                    .attr('width', width)
                    .attr('height', height)
                    .append('g');

                const g = svg.append('g')
                    .attr('transform', `translate(${width / 2}, ${height / 2})`);

                const zoom = d3.zoom().on('zoom', (event) => {
                    g.attr('transform', event.transform);
                });
                
                const svgSelection = d3.select('#tree-container svg');
                svgSelection.call(zoom);

                const treeLayout = d3.tree()
                    .nodeSize([130, 150])
                    .separation((a, b) => {
                        let sep = 1;
                        if (a.data.spouses && a.data.spouses.length > 0) {
                            sep = 1.8;
                        }
                        return (a.parent == b.parent ? 1 : 1.2) * sep;
                    });

                // --- Descendants (Downwards) ---
                const rootDesc = d3.hierarchy(data.descendants);
                treeLayout(rootDesc);

                const fixPositions = (node, shift = 0, yShift = 0) => {
                    node.x += shift;
                    

                    node.y += yShift;
                    
                    let nextShift = shift;
                    if (node.data.spouses && node.data.spouses.length > 0) {
                        nextShift += spouseOffset / 2;
                    }
                    if (node.children) {
                        node.children.forEach(child => fixPositions(child, nextShift, yShift));
                    }
                };

                fixPositions(rootDesc);

                // --- Ancestors (Upwards) ---
                // Only render ancestors if they exist (not in Forest View)
                let rootAnc = null;
                if (data.ancestors) {
                    rootAnc = d3.hierarchy(data.ancestors);
                    treeLayout(rootAnc);
                    rootAnc.each(d => d.y = -d.y);
                }

                const descendantsNodes = rootDesc.descendants();
                const ancestorsNodes = rootAnc ? rootAnc.descendants().slice(1) : [];
                
                // --- Siblings & Centering ---
                const siblings = data.siblings || [];
                
                const rootWrapper = { 
                    data: data.descendants, 
                    isRoot: true,
                    node: rootDesc 
                };
                
                const siblingWrappers = siblings.map(s => ({
                    data: s,
                    isRoot: false
                }));
                
                const currentGeneration = [rootWrapper, ...siblingWrappers];
                
                currentGeneration.sort((a, b) => {
                    const dateA = a.data.birth_date || 0;
                    const dateB = b.data.birth_date || 0;
                    return dateA - dateB;
                });
                
                const gap = 50;
                const nodeWidth = 50;
                
                let currentX = 0;
                const positions = [];
                
                currentGeneration.forEach((item, index) => {
                    const hasSpouse = item.data.spouses && item.data.spouses.length > 0;
                    const width = hasSpouse ? (nodeWidth + spouseOffset) : nodeWidth;
                    
                    positions.push({
                        item: item,
                        width: width,
                        x: currentX + (width / 2)
                    });
                    
                    currentX += width + gap;
                });
                
                const totalWidth = currentX - gap;
                const startX = -totalWidth / 2;
                
                const siblingNodes = [];
                
                positions.forEach(pos => {
                    const newX = startX + pos.x - (pos.width / 2);
                    
                    if (pos.item.isRoot) {
                        const shiftX = newX - pos.item.node.x;
                        pos.item.node.each(d => d.x += shiftX);
                        if (rootAnc) rootAnc.x = newX;
                    } else {
                        siblingNodes.push({
                            data: pos.item.data,
                            x: newX,
                            y: 0,
                            depth: 0,
                            height: 0,
                            parent: null
                        });
                    }
                });

                const allNodes = [...descendantsNodes, ...ancestorsNodes, ...siblingNodes];

                const descendantsLinks = rootDesc.links();
                const ancestorsLinks = rootAnc ? rootAnc.links() : [];
                
                const linkGenerator = (d, direction) => {
                    let sourceX = d.source.x;
                    let sourceY = d.source.y;
                    let targetX = d.target.x;
                    let targetY = d.target.y;

                    if (direction === 'descendant') {
                        if (d.source.data.spouses && d.source.data.spouses.length > 0) {
                            sourceX += spouseOffset / 2;
                        }
                    }
                    else if (direction === 'ancestor') {
                        if (d.target.data.spouses && d.target.data.spouses.length > 0) {
                            targetX += spouseOffset / 2;
                        }
                    }

                    return "M" + sourceX + "," + sourceY
                         + "C" + sourceX + "," + (sourceY + targetY) / 2
                         + " " + targetX + "," + (sourceY + targetY) / 2
                         + " " + targetX + "," + targetY;
                };

                g.selectAll('.link-desc')
                    .data(descendantsLinks)
                    .enter().append('path')
                    .attr('class', 'link-desc')
                    .attr('d', d => linkGenerator(d, 'descendant'))
                    .attr('fill', 'none')
                    .attr('stroke', '#9ca3af')
                    .attr('stroke-width', 2)
                    .attr('opacity', d => d.source.data.is_virtual ? 0 : 1); // Hide lines from virtual root

                g.selectAll('.link-anc')
                    .data(ancestorsLinks)
                    .enter().append('path')
                    .attr('class', 'link-anc')
                    .attr('d', d => linkGenerator(d, 'ancestor'))
                    .attr('fill', 'none')
                    .attr('stroke', '#9ca3af')
                    .attr('stroke', '#9ca3af')
                    .attr('stroke-width', 2);

                // --- Extra Links (for disconnected trees) ---
                if (data.extra_links && data.extra_links.length > 0) {
                    const extraLinksData = data.extra_links.map(link => {
                        let sourceNode = allNodes.find(n => String(n.data.id) === String(link.source));
                        let targetNode = allNodes.find(n => String(n.data.id) === String(link.target));
                        let targetIsSpouse = false;
                        let sourceIsSpouse = false;

                        // If target not found, check spouses
                        if (!targetNode) {
                            targetNode = allNodes.find(n => n.data.spouses && n.data.spouses.some(s => String(s.id) === String(link.target)));
                            if (targetNode) targetIsSpouse = true;
                        }

                        // If source not found, check spouses (less likely for parent-child, but possible)
                        if (!sourceNode) {
                            sourceNode = allNodes.find(n => n.data.spouses && n.data.spouses.some(s => String(s.id) === String(link.source)));
                            if (sourceNode) sourceIsSpouse = true;
                        }

                        if (sourceNode && targetNode) {
                            return { source: sourceNode, target: targetNode, targetIsSpouse, sourceIsSpouse };
                        }
                        return null;
                    }).filter(l => l !== null);

                    g.selectAll('.link-extra')
                        .data(extraLinksData)
                        .enter().append('path')
                        .attr('class', 'link-extra')
                        .attr('d', d => {
                             let sourceX = d.source.x;
                             // Start from between parents if spouses exist
                             if (d.source.data.spouses && d.source.data.spouses.length > 0) {
                                 sourceX += spouseOffset / 2;
                             } else if (d.sourceIsSpouse) {
                                 sourceX += spouseOffset;
                             }
                             
                             const sourceY = d.source.y;
                             const targetX = d.target.x + (d.targetIsSpouse ? spouseOffset : 0);
                             const targetY = d.target.y;

                             return "M" + sourceX + "," + sourceY
                                  + "C" + sourceX + "," + (sourceY + targetY) / 2
                                  + " " + targetX + "," + (sourceY + targetY) / 2
                                  + " " + targetX + "," + targetY;
                        })
                        .attr('fill', 'none')
                        .attr('stroke', '#9ca3af')
                        .attr('stroke-width', 2)
                        .attr('stroke-dasharray', '5,5'); // Dashed line for visual distinction
                }

                if (rootAnc && rootAnc.children && rootAnc.children.length > 0) {
                    const parentNode = rootAnc.children[0]; 
                    
                    const siblingLinks = siblingNodes.map(sibling => {
                        return {
                            source: sibling,
                            target: parentNode
                        };
                    });

                    g.selectAll('.link-sibling')
                        .data(siblingLinks)
                        .enter().append('path')
                        .attr('class', 'link-sibling')
                        .attr('d', d => {
                            const sourceX = d.source.x;
                            const sourceY = d.source.y;
                            const targetX = d.target.x;
                            const targetY = d.target.y;
                            
                             let finalTargetX = targetX;
                             if (d.target.data.spouses && d.target.data.spouses.length > 0) {
                                 finalTargetX += spouseOffset / 2;
                             }
                             
                             let finalSourceX = sourceX;
                             if (d.source.data.spouses && d.source.data.spouses.length > 0) {
                                 finalSourceX += spouseOffset / 2;
                             }

                            return "M" + finalSourceX + "," + sourceY
                                 + "C" + finalSourceX + "," + (sourceY + targetY) / 2
                                 + " " + finalTargetX + "," + (sourceY + targetY) / 2
                                 + " " + finalTargetX + "," + targetY;
                        })
                        .attr('fill', 'none')
                        .attr('stroke', '#9ca3af')
                        .attr('stroke-width', 2);
                }

                const nodeGroup = g.selectAll('.node')
                    .data(allNodes)
                    .enter().append('g')
                    .attr('class', 'node cursor-pointer')
                    .attr('transform', d => `translate(${d.x},${d.y})`)
                    .attr('opacity', d => d.data.is_virtual ? 0 : 1) // Hide virtual root
                    .attr('pointer-events', d => d.data.is_virtual ? 'none' : 'all'); // Disable clicks on virtual root

                nodeGroup.append('circle')
                    .attr('r', 25)
                    .attr('fill', '#fff')
                    .attr('stroke', d => {
                        if (String(d.data.id) === String(rootId)) return '#f59e0b'; // Highlight root person (amber-500)
                        return d.data.gender === 1 ? '#3b82f6' : (d.data.gender === 2 ? '#ec4899' : '#9ca3af');
                    })
                    .attr('stroke-width', d => String(d.data.id) === String(rootId) ? 4 : 2) // Thicker border for root
                    .on('click', (event, d) => {
                        if (!d.data.is_virtual) {
                            window.location.href = `/trees/{{ $tree->id }}/person/${d.data.id}`;
                        }
                    });

                nodeGroup.append('clipPath')
                    .attr('id', d => `clip-${d.data.id}`)
                    .append('circle')
                    .attr('r', 25);

                nodeGroup.append('image')
                    .attr('xlink:href', d => d.data.photo)
                    .attr('x', -25)
                    .attr('y', -25)
                    .attr('width', 50)
                    .attr('height', 50)
                    .attr('clip-path', d => `url(#clip-${d.data.id})`)
                    .attr('preserveAspectRatio', 'xMidYMid slice')
                    .on('click', (event, d) => {
                        if (!d.data.is_virtual) {
                            window.location.href = `/trees/{{ $tree->id }}/person/${d.data.id}`;
                        }
                    });

                nodeGroup.append('text')
                    .attr('dy', 45)
                    .attr('text-anchor', 'middle')
                    .text(d => d.data.name)
                    .attr('class', d => String(d.data.id) === String(rootId)
                        ? 'text-xs font-bold fill-amber-600 dark:fill-amber-400' 
                        : 'text-xs font-bold fill-gray-900 dark:fill-gray-100');

                nodeGroup.each(function(d) {
                    if (d.data.spouses && d.data.spouses.length > 0) {
                        const spouseGroup = d3.select(this).append('g')
                            .attr('transform', `translate(${spouseOffset}, 0)`);

                        d3.select(this).append('line')
                            .attr('x1', 25)
                            .attr('y1', 0)
                            .attr('x2', spouseOffset - 25)
                            .attr('y2', 0)
                            .attr('stroke', '#ef4444')
                            .attr('stroke-width', 2);

                        spouseGroup.append('circle')
                            .attr('r', 25)
                            .attr('fill', '#fff')
                            .attr('stroke', d.data.spouses[0].gender === 1 ? '#3b82f6' : (d.data.spouses[0].gender === 2 ? '#ec4899' : '#9ca3af'))
                            .attr('stroke-width', 2);

                        spouseGroup.append('clipPath')
                            .attr('id', `clip-spouse-${d.data.spouses[0].id}`)
                            .append('circle')
                            .attr('r', 25);

                        spouseGroup.append('image')
                            .attr('xlink:href', d.data.spouses[0].photo)
                            .attr('x', -25)
                            .attr('y', -25)
                            .attr('width', 50)
                            .attr('height', 50)
                            .attr('clip-path', `url(#clip-spouse-${d.data.spouses[0].id})`)
                            .attr('preserveAspectRatio', 'xMidYMid slice')
                            .on('click', (event) => {
                                event.stopPropagation();
                                window.location.href = `/trees/{{ $tree->id }}/person/${d.data.spouses[0].id}`;
                            });

                        spouseGroup.append('text')
                            .attr('dy', 45)
                            .attr('text-anchor', 'middle')
                            .text(d.data.spouses[0].name)
                            .attr('class', 'text-xs font-medium fill-gray-700 dark:fill-gray-300');
                    }
                });

                const resetZoom = () => {
                    // Calculate bounds excluding virtual root nodes
                    const visibleNodes = allNodes.filter(d => !d.data.is_virtual);
                    
                    if (visibleNodes.length === 0) return;
                    
                    let minX = Infinity, maxX = -Infinity;
                    let minY = Infinity, maxY = -Infinity;
                    
                    visibleNodes.forEach(d => {
                        const nodeWidth = 50;
                        const nodeHeight = 50;
                        minX = Math.min(minX, d.x - nodeWidth/2);
                        maxX = Math.max(maxX, d.x + nodeWidth/2);
                        minY = Math.min(minY, d.y - nodeHeight/2);
                        maxY = Math.max(maxY, d.y + nodeHeight/2);
                    });
                    
                    const parent = svg.node().parentElement;
                    const fullWidth = parent.clientWidth;
                    const fullHeight = parent.clientHeight;
                    
                    svg.attr('width', fullWidth).attr('height', fullHeight);
                    // Reset transform to center first
                    g.attr('transform', `translate(${fullWidth / 2}, ${fullHeight / 2})`);
                    
                    const width = maxX - minX;
                    const height = maxY - minY;
                    const midX = minX + width / 2;
                    const midY = minY + height / 2;
                    
                    if (width === 0 || height === 0) return;
                    
                    const scale = 0.7 / Math.max(width / fullWidth, height / fullHeight);
                    
                    let translate;
                    
                    // Check if Forest View (Virtual Root)
                    if (data.descendants.is_virtual) {
                        // Align Top - use minY from visible nodes
                        const tx = fullWidth / 2 - midX * scale;
                        const ty = 50 - minY * scale;
                        translate = [tx, ty];
                    } else {
                        // Center Alignment (Default)
                        const tx = fullWidth / 2 - midX * scale;
                        const ty = fullHeight / 2 - midY * scale;
                        translate = [tx, ty];
                    }

                    svgSelection.transition()
                        .duration(750)
                        .call(zoom.transform, d3.zoomIdentity.translate(translate[0], translate[1]).scale(scale));
                };

                setTimeout(resetZoom, 100);
                
                // Remove old listener to prevent duplicates if re-initialized
                const resetBtn = document.getElementById('reset-zoom');
                const newResetBtn = resetBtn.cloneNode(true);
                resetBtn.parentNode.replaceChild(newResetBtn, resetBtn);
                newResetBtn.addEventListener('click', resetZoom);
                
                window.addEventListener('resize', () => {
                    const parent = container;
                    const fullWidth = parent.clientWidth;
                    const fullHeight = parent.clientHeight;
                    svg.attr('width', fullWidth).attr('height', fullHeight);
                });
            };

            // Initial Render
            renderGraph(initialData);

            // Listen for updates
            Livewire.on('graph-updated', ({ data }) => {
                renderGraph(data);
            });
        });
    </script>
</div>

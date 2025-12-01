<div class="h-full flex flex-col bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 shadow z-10">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $tree->name }} - Visualizer</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Root: {{ $rootPerson->full_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="toggleWholeTree" wire:loading.attr="disabled"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="toggleWholeTree">
                        {{ $isWholeTree ? 'Show Focused View' : 'Show Whole Tree' }}
                    </span>
                    <span wire:loading wire:target="toggleWholeTree">
                        Loading...
                    </span>
                </button>
                <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $rootPerson->id]) }}"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Back to Profile
                </a>
            </div>
        </div>
    </div>

    <div id="tree-container" class="flex-grow overflow-hidden relative">
        {{-- D3 Graph will be rendered here --}}
        <button id="reset-tree"
            class="absolute bottom-4 right-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 z-20">
            Reset Tree
        </button>
    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const initialData = @json($this->graphData);
            const rootId = @json($rootPerson->id);

            const collapsedNodeIds = new Set();
            let currentTransform = null;
            let clickTimer = null;

            const renderGraph = (data) => {
                const applyCollapse = (node) => {
                    const id = String(node.id);
                    const isCollapsed = collapsedNodeIds.has(id);

                    if (isCollapsed) {
                        if (node.children) {
                            node._children = node.children;
                            node.children = null;
                        }
                        if (node.spouses) {
                            node._spouses = node.spouses;
                            node.spouses = null;
                        }
                    } else {
                        if (node._children) {
                            node.children = node._children;
                            node._children = null;
                        }
                        if (node._spouses) {
                            node.spouses = node._spouses;
                            node._spouses = null;
                        }
                    }

                    // This part of the original code was problematic for recursion,
                    // as it only recursed on children of spouses, not spouses themselves.
                    // The new structure below handles both children and spouses correctly.
                    /*
                    if (node.spouses) {
                        node.spouses.forEach(spouse => {
                            if (isCollapsed) {
                                if (spouse.children) {
                                    spouse._children = spouse.children;
                                    spouse.children = null;
                                }
                            } else {
                                if (spouse._children) {
                                    spouse.children = spouse._children;
                                    spouse._children = null;
                                }
                            }

                            const spouseChildren = spouse.children || spouse._children;
                            if (spouseChildren) {
                                spouseChildren.forEach(applyCollapse);
                            }

                            if (spouse.spouses) {
                                spouse.spouses.forEach(applyCollapse);
                            }
                        });
                    }
                    */

                    const children = node.children || node._children;
                    if (children) {
                        children.forEach(applyCollapse);
                    }

                    const spouses = node.spouses || node._spouses;
                    if (spouses) {
                        spouses.forEach(applyCollapse);
                    }
                };
                applyCollapse(data.descendants);

                const handleClick = (event, personData) => {
                    event.stopPropagation();
                    if (clickTimer) {
                        clearTimeout(clickTimer);
                        clickTimer = null;
                        if (!personData.is_virtual) {
                            window.location.href = `/trees/{{ $tree->id }}/person/${personData.id}`;
                        }
                    } else {
                        clickTimer = setTimeout(() => {
                            clickTimer = null;
                            const id = String(personData.id);
                            if (collapsedNodeIds.has(id)) {
                                collapsedNodeIds.delete(id);
                            } else {
                                collapsedNodeIds.add(id);
                            }
                            renderGraph(data);
                        }, 250);
                    }
                };
                const container = document.getElementById('tree-container');
                const width = container.clientWidth;
                const height = container.clientHeight;
                const spouseOffset = 160;

                // Clear previous SVG if any
                // Save current transform
                const existingSvg = d3.select('#tree-container svg').node();
                if (existingSvg) {
                    currentTransform = d3.zoomTransform(existingSvg);
                }

                d3.select('#tree-container').selectAll('svg').remove();

                const svg = d3.select('#tree-container').append('svg')
                    .attr('width', width)
                    .attr('height', height)
                    .append('g');

                const g = svg.append('g')
                    .attr('transform', `translate(${width / 2}, ${height / 2})`);

                const zoom = d3.zoom()
                    .scaleExtent([0.05, 2])
                    .on('zoom', (event) => {
                        g.attr('transform', event.transform);
                    });

                const svgSelection = d3.select('#tree-container svg');
                svgSelection.call(zoom);

                if (currentTransform) {
                    svgSelection.call(zoom.transform, currentTransform);
                }

                // Helper to calculate total width of a spouse chain recursively
                const calculateSpouseChainWidth = (nodeData) => {
                    if (!nodeData.spouses || nodeData.spouses.length === 0) return 0;

                    let currentOffset = 0;
                    let prevRightEdge = 0; // Track right edge of previous spouse/children

                    const traverse = (spouses) => {
                        let chainWidth = 0;
                        // For width calculation, we simulate the layout
                        let localOffset = 0;
                        let localPrevRightEdge = 0;

                        spouses.forEach(spouse => {
                            let thisChildHalfWidth = 0;
                            if (spouse.children && spouse.children.length > 0) {
                                const childGap = 150;
                                const childrenWidth = (spouse.children.length - 1) * childGap;
                                thisChildHalfWidth = (childrenWidth / 2) + 40;
                            }

                            // Standard step based on children width
                            const padding = 20;
                            // Distance from previous spouse center to this spouse center
                            // We don't have prevChildHalfWidth easily available here without tracking
                            // But we can track right edge.

                            // Let's use the same logic as renderRecursive
                            // We need to determine where this spouse is placed (localOffset)

                            // 1. Gap from previous spouse
                            // We assume previous spouse is at `localOffset - step`? No.
                            // We track `localOffset` as the position of the CURRENT spouse.

                            // Initial step for first spouse
                            let step = 160;
                            if (localOffset === 0) {
                                // First spouse.
                                // Gap from Parent (0) to Spouse.
                                // Parent has no children width in this context (it's the node).
                                // But we need to accommodate Spouse's children.
                                step = Math.max(160, thisChildHalfWidth + 40);
                            } else {
                                // Subsequent spouse.
                                // Gap from Prev Spouse to Curr Spouse.
                                // Need to clear Prev Spouse's children + Curr Spouse's children.
                                // We can use localPrevRightEdge.
                                // Gap = (localOffset - prevPos)
                                // But simpler: step = max(160, ...)
                                // Let's simplify: just use the midpoint constraint which dominates.
                            }

                            // Midpoint Constraint:
                            // Child is at (0 + localOffset) / 2.
                            // We need Child > localPrevRightEdge.
                            // localOffset > 2 * localPrevRightEdge.

                            let pos = localOffset + 160; // Min step
                            if (localOffset > 0) {
                                // Ensure gap from previous
                                pos = Math.max(pos, localPrevRightEdge + thisChildHalfWidth + padding + 40);
                            }

                            // Apply Midpoint Constraint
                            pos = Math.max(pos, 2 * localPrevRightEdge);

                            // Update localOffset
                            localOffset = pos;

                            // Calculate this spouse's right edge
                            let myRightEdge = localOffset + thisChildHalfWidth;

                            // Add nested spouses width
                            if (spouse.spouses && spouse.spouses.length > 0) {
                                const nestedWidth = traverse(spouse.spouses);
                                // Nested spouses extend to the right
                                myRightEdge = Math.max(myRightEdge, localOffset + nestedWidth);
                            }

                            localPrevRightEdge = myRightEdge;
                        });

                        return localPrevRightEdge;
                    };

                    return traverse(nodeData.spouses);
                };

                const treeLayout = d3.tree()
                    .nodeSize([130, 200])
                    .separation((a, b) => {
                        const aWidth = calculateSpouseChainWidth(a.data);
                        const bWidth = calculateSpouseChainWidth(b.data);
                        const widthFactor = (aWidth + bWidth) / 130;
                        let sep = 1.2;
                        if (widthFactor > 0) {
                            sep += widthFactor * 0.8;
                        }
                        return (a.parent == b.parent ? 1 : 1.2) * sep;
                    });





                // --- Descendants (Downwards) ---
                const rootDesc = d3.hierarchy(data.descendants);
                treeLayout(rootDesc);

                const getSpouseCenterShift = (nodeData) => {
                    if (!nodeData.spouses || nodeData.spouses.length === 0) return 0;

                    let currentOffset = 0;
                    let prevRightEdge = 0;
                    let totalPos = 0;
                    let count = 0;

                    nodeData.spouses.forEach(spouse => {
                        let thisChildHalfWidth = 0;
                        if (spouse.children && spouse.children.length > 0) {
                            const childGap = 150;
                            const childrenWidth = (spouse.children.length - 1) * childGap;
                            thisChildHalfWidth = (childrenWidth / 2) + 40;
                        }

                        const padding = 20;
                        let pos = currentOffset + 160;

                        if (currentOffset > 0) {
                            pos = Math.max(pos, prevRightEdge + thisChildHalfWidth + padding);
                        } else {
                            pos = Math.max(pos, thisChildHalfWidth + 40);
                        }

                        pos = Math.max(pos, 2 * prevRightEdge);

                        currentOffset = pos;
                        totalPos += pos;
                        count++;

                        let myRightEdge = pos + thisChildHalfWidth;
                        if (spouse.spouses && spouse.spouses.length > 0) {
                            const nestedWidth = calculateSpouseChainWidth(spouse);
                            myRightEdge = Math.max(myRightEdge, pos + nestedWidth);
                        }

                        prevRightEdge = myRightEdge;
                    });

                    if (count === 0) return 0;
                    return (totalPos / count) / 2;
                };

                const fixPositions = (node, shift = 0, yShift = 0) => {
                    node.x += shift;
                    node.y += yShift;

                    let nextShift = shift;

                    // Use the new center shift calculation
                    const centerShift = getSpouseCenterShift(node.data);

                    if (centerShift > 0) {
                        nextShift += centerShift;
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
                    // Use the new width calculation
                    const chainWidth = calculateSpouseChainWidth(item.data);
                    const width = nodeWidth + chainWidth;

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

                // Identify children handled by spouses to exclude from D3 rendering
                const spouseChildrenIds = new Set();
                const collectSpouseChildren = (node) => {
                    if (node.spouses) {
                        node.spouses.forEach(spouse => {
                            if (spouse.children) {
                                spouse.children.forEach(child => spouseChildrenIds.add(String(child.id)));
                            }
                            collectSpouseChildren(spouse);
                        });
                    }
                    if (node.children) {
                        node.children.forEach(collectSpouseChildren);
                    }
                };
                collectSpouseChildren(data.descendants);

                // Filter descendantsNodes and links
                const descendantsLinks = rootDesc.links();
                const filteredDescendantsNodes = descendantsNodes.filter(d => !spouseChildrenIds.has(String(d.data.id)));
                const filteredDescendantsLinks = descendantsLinks.filter(d => !spouseChildrenIds.has(String(d.target.data.id)));

                const allNodes = [...filteredDescendantsNodes, ...ancestorsNodes, ...siblingNodes];

                const ancestorsLinks = rootAnc ? rootAnc.links() : [];

                const linkGenerator = (d, direction) => {
                    let sourceX = d.source.x;
                    let sourceY = d.source.y;
                    let targetX = d.target.x;
                    let targetY = d.target.y;

                    if (direction === 'descendant') {
                        if (d.source.data.spouses && d.source.data.spouses.length > 0) {
                            // Start link from center of family group (Parents only)
                            // We use getSpouseCenterShift to match the children centering logic
                            const centerShift = getSpouseCenterShift(d.source.data);
                            sourceX += centerShift;
                        }
                    }
                    else if (direction === 'ancestor') {
                        if (d.target.data.spouses && d.target.data.spouses.length > 0) {
                            // End link at center of family group
                            const chainWidth = calculateSpouseChainWidth(d.target.data);
                            targetX += chainWidth / 2;
                        }
                    }

                    return "M" + sourceX + "," + sourceY
                        + "C" + sourceX + "," + (sourceY + targetY) / 2
                        + " " + targetX + "," + (sourceY + targetY) / 2
                        + " " + targetX + "," + targetY;
                };

                g.selectAll('.link-desc')
                    .data(filteredDescendantsLinks)
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
                                sourceX += (spouseOffset * d.source.data.spouses.length) / 2;
                            } else if (d.sourceIsSpouse) {
                                // Approximate logic for sourceIsSpouse - might need refinement if multiple spouses
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
                                finalTargetX += (spouseOffset * d.target.data.spouses.length) / 2;
                            }

                            let finalSourceX = sourceX;
                            if (d.source.data.spouses && d.source.data.spouses.length > 0) {
                                finalSourceX += (spouseOffset * d.source.data.spouses.length) / 2;
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
                    .on('click', (event, d) => handleClick(event, d.data));

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
                    .on('click', (event, d) => handleClick(event, d.data));

                nodeGroup.append('text')
                    .attr('dy', 45)
                    .attr('text-anchor', 'middle')
                    .text(d => d.data.name)
                    .attr('class', d => String(d.data.id) === String(rootId)
                        ? 'text-xs font-bold fill-amber-600 dark:fill-amber-400'
                        : 'text-xs font-bold fill-gray-900 dark:fill-gray-100');

                nodeGroup.each(function (d) {
                    if (d.data.spouses && d.data.spouses.length > 0) {
                        const renderRecursive = (spouses, parentSelection, parentX, depth = 0) => {
                            let currentOffset = 0; // Local offset for this level
                            let prevRightEdge = 0; // Track right edge of previous element in this group

                            spouses.forEach((spouse) => {
                                let thisChildHalfWidth = 0;
                                if (spouse.children && spouse.children.length > 0) {
                                    const childGap = 150;
                                    const childrenWidth = (spouse.children.length - 1) * childGap;
                                    thisChildHalfWidth = (childrenWidth / 2) + 40;
                                }

                                const padding = 20;

                                // Calculate Position
                                // Min distance from Parent (0) or Previous Spouse
                                let pos = currentOffset + 160;

                                if (currentOffset > 0) {
                                    // Ensure gap from previous spouse
                                    // We need pos > prevRightEdge + thisChildHalfWidth + padding
                                    pos = Math.max(pos, prevRightEdge + thisChildHalfWidth + padding);
                                } else {
                                    // First spouse
                                    // Ensure we clear Parent (0) + Spouse Children
                                    pos = Math.max(pos, thisChildHalfWidth + 40);
                                }

                                // Midpoint Constraint:
                                // Child is at (0 + pos) / 2.
                                // We need Child > prevRightEdge.
                                // pos > 2 * prevRightEdge.
                                pos = Math.max(pos, 2 * prevRightEdge);

                                currentOffset = pos;
                                const myOffset = currentOffset;

                                console.log(`Rendering ${spouse.name}: ParentX=${parentX}, MyOffset=${myOffset}, PrevRight=${prevRightEdge}`);

                                // Line styling
                                let strokeColor = '#ef4444';
                                let strokeDash = 'none';
                                let strokeWidth = 2;

                                if (depth > 0) {
                                    strokeColor = '#ef4444';
                                    strokeDash = '2,2';
                                } else {
                                    if (spouse.relationship_subtype === 6) {
                                        strokeDash = '5,5';
                                    }
                                }

                                // Append Line FIRST (so it's behind the node)
                                parentSelection.append('line')
                                    .attr('x1', parentX + 25) // parentX is 0 in local context
                                    .attr('y1', 0)
                                    .attr('x2', myOffset - 25)
                                    .attr('y2', 0)
                                    .attr('stroke', strokeColor)
                                    .attr('stroke-width', strokeWidth)
                                    .attr('stroke-dasharray', strokeDash);

                                // Append Spouse Group SECOND
                                const spouseGroup = parentSelection.append('g')
                                    .attr('transform', `translate(${myOffset}, 0)`);

                                spouseGroup.append('circle').attr('r', 25).attr('fill', '#fff').attr('stroke', spouse.gender === 1 ? '#3b82f6' : (spouse.gender === 2 ? '#ec4899' : '#9ca3af')).attr('stroke-width', 2);
                                spouseGroup.append('clipPath').attr('id', `clip-spouse-${spouse.id}`).append('circle').attr('r', 25);
                                spouseGroup.append('image').attr('xlink:href', spouse.photo).attr('x', -25).attr('y', -25).attr('width', 50).attr('height', 50).attr('clip-path', `url(#clip-spouse-${spouse.id})`).attr('preserveAspectRatio', 'xMidYMid slice').on('click', (e) => handleClick(e, spouse));
                                spouseGroup.append('text').attr('dy', 45).attr('text-anchor', 'middle').text(spouse.name).attr('class', 'text-xs font-bold fill-gray-700 dark:fill-gray-300');

                                // Render Children
                                let myRightEdge = myOffset + thisChildHalfWidth;

                                if (spouse.children && spouse.children.length > 0) {
                                    const midX = (parentX - myOffset) / 2; // parentX is 0
                                    const childGap = 150;
                                    const childrenWidth = (spouse.children.length - 1) * childGap;
                                    const startX = midX - (childrenWidth / 2);

                                    spouse.children.forEach((child, i) => {
                                        const childX = startX + (i * childGap);
                                        const childY = 150;

                                        // Append Link FIRST (so it's behind the child node)
                                        spouseGroup.append('path')
                                            .attr('d', `M${midX},0 C${midX},${childY / 2} ${childX},${childY / 2} ${childX},${childY}`)
                                            .attr('fill', 'none')
                                            .attr('stroke', '#9ca3af')
                                            .attr('stroke-width', 2)
                                            .attr('stroke-dasharray', strokeDash);

                                        // Append Child Group SECOND
                                        const childGroup = spouseGroup.append('g').attr('transform', `translate(${childX}, ${childY})`);

                                        childGroup.append('circle').attr('r', 25).attr('fill', '#fff').attr('stroke', child.gender === 1 ? '#3b82f6' : (child.gender === 2 ? '#ec4899' : '#9ca3af')).attr('stroke-width', 2);
                                        childGroup.append('clipPath').attr('id', `clip-child-${child.id}`).append('circle').attr('r', 25);
                                        childGroup.append('image').attr('xlink:href', child.photo).attr('x', -25).attr('y', -25).attr('width', 50).attr('height', 50).attr('clip-path', `url(#clip-child-${child.id})`).attr('preserveAspectRatio', 'xMidYMid slice').on('click', (e) => handleClick(e, child));
                                        childGroup.append('text').attr('dy', 45).attr('text-anchor', 'middle').text(child.name).attr('class', 'text-xs font-bold fill-gray-900 dark:fill-gray-100');
                                    });
                                }

                                // Render Nested Spouses
                                if (spouse.spouses && spouse.spouses.length > 0) {
                                    // Pass spouseGroup as parentSelection for nested spouses
                                    renderRecursive(spouse.spouses, spouseGroup, 0, depth + 1);

                                    // We need to know the width of the nested spouses to update myRightEdge
                                    const nestedWidth = calculateSpouseChainWidth(spouse);
                                    myRightEdge = Math.max(myRightEdge, myOffset + nestedWidth);
                                }

                                prevRightEdge = myRightEdge;
                            });
                        };

                        renderRecursive(d.data.spouses, d3.select(this), 0);
                    }
                });

                const fitToScreen = () => {
                    // Calculate bounds excluding virtual root nodes
                    const visibleNodes = allNodes.filter(d => !d.data.is_virtual);

                    if (visibleNodes.length === 0) return;

                    let minX = Infinity, maxX = -Infinity;
                    let minY = Infinity, maxY = -Infinity;

                    visibleNodes.forEach(d => {
                        const nodeWidth = 50;
                        const nodeHeight = 50;
                        minX = Math.min(minX, d.x - nodeWidth / 2);
                        maxX = Math.max(maxX, d.x + nodeWidth / 2);
                        minY = Math.min(minY, d.y - nodeHeight / 2);
                        maxY = Math.max(maxY, d.y + nodeHeight / 2);
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

                    const scale = Math.min(2, 0.9 / Math.max(width / fullWidth, height / fullHeight));

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



                setTimeout(fitToScreen, 100);

                // Remove old listener to prevent duplicates if re-initialized
                const resetBtn = document.getElementById('reset-tree');
                const newResetBtn = resetBtn.cloneNode(true);
                resetBtn.parentNode.replaceChild(newResetBtn, resetBtn);
                newResetBtn.addEventListener('click', () => {
                    collapsedNodeIds.clear();
                    currentTransform = null;
                    renderGraph(initialData);
                });

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
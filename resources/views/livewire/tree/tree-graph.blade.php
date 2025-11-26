<div class="h-screen flex flex-col">
    <div class="p-4 bg-white shadow flex justify-between items-center z-10">
        <div>
            <h1 class="text-xl font-bold">{{ $tree->name }} - Visualizer</h1>
            <p class="text-sm text-gray-500">Root: {{ $rootPerson->full_name }}</p>
        </div>
        <a href="{{ route('person.show', ['tree' => $tree->id, 'person' => $rootPerson->id]) }}" class="px-4 py-2 border rounded hover:bg-gray-100">
            Back to Profile
        </a>
    </div>

    <div id="tree-container" class="flex-grow bg-gray-50 overflow-hidden relative">
        {{-- D3 Graph will be rendered here --}}
    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const data = @json($this->graphData);
            const container = document.getElementById('tree-container');
            const width = container.clientWidth;
            const height = container.clientHeight;

            // Zoom behavior
            const zoom = d3.zoom()
                .scaleExtent([0.1, 3])
                .on('zoom', (event) => {
                    g.attr('transform', event.transform);
                });

            const svg = d3.select('#tree-container').append('svg')
                .attr('width', width)
                .attr('height', height)
                .call(zoom)
                .append('g');

            const g = svg.append('g')
                .attr('transform', `translate(${width / 2}, 50)`);

            // Tree Layout
            const treeLayout = d3.tree().nodeSize([150, 100]);
            const root = d3.hierarchy(data);
            treeLayout(root);

            // Links
            g.selectAll('.link')
                .data(root.links())
                .enter().append('path')
                .attr('class', 'link')
                .attr('d', d3.linkVertical()
                    .x(d => d.x)
                    .y(d => d.y)
                )
                .attr('fill', 'none')
                .attr('stroke', '#ccc')
                .attr('stroke-width', 2);

            // Nodes
            const node = g.selectAll('.node')
                .data(root.descendants())
                .enter().append('g')
                .attr('class', 'node cursor-pointer')
                .attr('transform', d => `translate(${d.x},${d.y})`)
                .on('click', (event, d) => {
                    window.location.href = `/trees/{{ $tree->id }}/person/${d.data.id}`;
                });

            // Circle
            node.append('circle')
                .attr('r', 20)
                .attr('fill', d => d.data.gender === 'Male' ? '#bfdbfe' : (d.data.gender === 'Female' ? '#fbcfe8' : '#e5e7eb'))
                .attr('stroke', '#666')
                .attr('stroke-width', 1);

            // Label
            node.append('text')
                .attr('dy', 35)
                .attr('text-anchor', 'middle')
                .text(d => d.data.name)
                .attr('class', 'text-xs font-medium fill-gray-700');

        });
    </script>
</div>

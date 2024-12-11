<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERD Diagram</title>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        .node {
            stroke: #333;
            stroke-width: 1.5px;
        }

        .link {
            stroke: #999;
            stroke-opacity: 0.6;
        }

        text {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
    </style>
</head>
<body>
<h1>Entity-Relationship Diagram</h1>
<div id="erd-container" style="width: 100%; height: 600px;"></div>

<script>
    // Example data structure
    const data = @json($diagramData);

    const width = document.getElementById('erd-container').clientWidth;
    const height = 600;

    const svg = d3.select('#erd-container')
        .append('svg')
        .attr('width', width)
        .attr('height', height);

    const simulation = d3.forceSimulation(data.nodes)
        .force('link', d3.forceLink(data.links).id(d => d.id).distance(100))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2));

    const link = svg.append('g')
        .attr('class', 'links')
        .selectAll('line')
        .data(data.links)
        .enter()
        .append('line')
        .attr('class', 'link')
        .style('stroke-width', d => Math.sqrt(d.value));

    const node = svg.append('g')
        .attr('class', 'nodes')
        .selectAll('circle')
        .data(data.nodes)
        .enter()
        .append('circle')
        .attr('class', 'node')
        .attr('r', 10)
        .call(drag(simulation));

    const text = svg.append('g')
        .attr('class', 'texts')
        .selectAll('text')
        .data(data.nodes)
        .enter()
        .append('text')
        .text(d => d.name)
        .attr('x', 12)
        .attr('y', '.31em');

    simulation.on('tick', () => {
        link.attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

        node.attr('cx', d => d.x)
            .attr('cy', d => d.y);

        text.attr('x', d => d.x + 12)
            .attr('y', d => d.y + 3);
    });

    function drag(simulation) {
        return d3.drag()
            .on('start', event => {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                event.subject.fx = event.subject.x;
                event.subject.fy = event.subject.y;
            })
            .on('drag', event => {
                event.subject.fx = event.x;
                event.subject.fy = event.y;
            })
            .on('end', event => {
                if (!event.active) simulation.alphaTarget(0);
                event.subject.fx = null;
                event.subject.fy = null;
            });
    }
</script>
</body>
</html>

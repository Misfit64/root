<?php

namespace App\Services\TreeTraversal;

interface TreeTraversalStrategy
{
    /**
     * Build descendants tree structure
     *
     * @param \App\Models\Person $person Starting person
     * @param int $depth Current depth
     * @param int $maxDepth Maximum depth to traverse
     * @param array &$visitedIds Reference to visited IDs array
     * @param callable $formatCallback Callback to format node data (receives $person, $depth)
     * @param callable|null $onVisitedChild Callback when a visited child is encountered
     * @return array|null Node structure or null if depth exceeded
     */
    public function buildDescendants($person, int $depth, int $maxDepth, array &$visitedIds, callable $formatCallback, ?callable $onVisitedChild = null): ?array;

    /**
     * Build ancestors tree structure
     *
     * @param \App\Models\Person $person Starting person
     * @param int $depth Current depth
     * @param int $maxDepth Maximum depth to traverse
     * @param array &$visitedIds Reference to visited IDs array
     * @param callable $formatCallback Callback to format node data
     * @return array|null Node structure or null if depth exceeded
     */
    public function buildAncestors($person, int $depth, int $maxDepth, array &$visitedIds, callable $formatCallback): ?array;
    /**
     * Build ancestors tree structure (inverted - upward from person)
     *
     * @param \App\Models\Person $person Starting person
     * @param int $maxDepth Maximum depth to traverse upward
     * @param array &$visitedIds Reference to visited IDs array
     * @param callable $formatCallback Callback to format node data
     * @return array|null Node structure or null if depth exceeded
     */
    public function buildAncestorsUpward($person, int $maxDepth, array &$visitedIds, callable $formatCallback): ?array;
}

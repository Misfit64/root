<?php

namespace App\Services\TreeTraversal;

use App\Models\Person;

class DfsTraversal implements TreeTraversalStrategy
{
    /**
     * Build descendants using Depth-First Search (recursive)
     */
    public function buildDescendants($person, int $depth, int $maxDepth, array &$visitedIds, callable $formatCallback, ?callable $onVisitedChild = null): ?array
    {
        if ($depth > $maxDepth) return null;
        if (in_array($person->id, $visitedIds)) return null;

        $visitedIds[] = $person->id;

        $node = $formatCallback($person, $depth);
        $children = $person->children()->orderBy('birth_date', 'asc')->get();
        
        if ($children->count() > 0) {
            $node['children'] = [];
            foreach ($children as $child) {
                if (in_array($child->id, $visitedIds)) {
                    if ($onVisitedChild) {
                        $onVisitedChild($child, $person, $depth);
                    }
                    continue;
                }

                $childNode = $this->buildDescendants($child, $depth + 1, $maxDepth, $visitedIds, $formatCallback, $onVisitedChild);
                if ($childNode) {
                    $node['children'][] = $childNode;
                }
            }
        }

        return $node;
    }

    /**
     * Build ancestors using Depth-First Search (recursive)
     */
    public function buildAncestors($person, int $depth, int $maxDepth, array &$visitedIds, callable $formatCallback): ?array
    {
        if ($depth > $maxDepth) return null;

        $node = $formatCallback($person);
        $parents = $person->parents;

        if ($parents->count() > 0) {
            $node['children'] = [];
            $processedParents = [];

            foreach ($parents as $parent) {
                if (in_array($parent->id, $processedParents)) continue;

                // Check if this parent has a spouse in the parents list
                $spouse = $parents->first(function ($p) use ($parent) {
                    return $p->id !== $parent->id && $parent->spouses->contains('id', $p->id);
                });

                if ($spouse) {
                    $primary = ($parent->gender?->value === 1) ? $parent : $spouse;
                    $secondary = ($primary->id === $parent->id) ? $spouse : $parent;

                    if (in_array($primary->id, $processedParents)) continue;

                    $processedParents[] = $primary->id;
                    $processedParents[] = $secondary->id;
                    
                    $coupleNode = $formatCallback($primary);
                    $coupleNode['children'] = [];
                    
                    // Primary's parents
                    foreach ($primary->parents as $pp) {
                         $ppNode = $this->buildAncestors($pp, $depth + 1, $maxDepth, $visitedIds, $formatCallback);
                         if ($ppNode) $coupleNode['children'][] = $ppNode;
                    }
                    
                    // Secondary's parents
                    foreach ($secondary->parents as $sp) {
                         $spNode = $this->buildAncestors($sp, $depth + 1, $maxDepth, $visitedIds, $formatCallback);
                         if ($spNode) $coupleNode['children'][] = $spNode;
                    }
                    
                    $node['children'][] = $coupleNode;

                } else {
                    // Single parent (or spouse not in the list of parents)
                    $processedParents[] = $parent->id;
                    $parentNode = $this->buildAncestors($parent, $depth + 1, $maxDepth, $visitedIds, $formatCallback);
                    if ($parentNode) {
                        $node['children'][] = $parentNode;
                    }
                }
            }
        }


        return $node;
    }

    /**
     * Build ancestors upward from person (inverted structure for integration)
     */
    public function buildAncestorsUpward($person, int $maxDepth, array &$visitedIds, callable $formatCallback): ?array
    {
        if ($maxDepth <= 0) return null;
        if (in_array($person->id, $visitedIds)) return null;

        $visitedIds[] = $person->id;
        
        $node = $formatCallback($person);
        $parents = $person->parents;

        if ($parents->count() > 0) {
            $node['children'] = [];
            
            foreach ($parents as $parent) {
                $parentNode = $this->buildAncestorsUpward($parent, $maxDepth - 1, $visitedIds, $formatCallback);
                if ($parentNode) {
                    $node['children'][] = $parentNode;
                }
            }
        }

        return $node;
    }
}

<?php

namespace App\Services\TreeTraversal;

use App\Models\Person;

class BfsTraversal implements TreeTraversalStrategy
{
    /**
     * Build descendants using Breadth-First Search (queue-based)
     */
    public function buildDescendants($person, int $depth, int $maxDepth, array &$visitedIds, callable $formatCallback, ?callable $onVisitedChild = null): ?array
    {
        if ($depth > $maxDepth) return null;
        if (in_array($person->id, $visitedIds)) return null;

        $visitedIds[] = $person->id;
        $rootNode = $formatCallback($person, $depth);
        
        // Use a queue for BFS: [node reference, person model, current depth]
        $queue = [];
        $rootNode['children'] = [];
        
        // Add root's children to queue
        $children = $person->children()->orderBy('birth_date', 'asc')->get();
        foreach ($children as $child) {
            if (!in_array($child->id, $visitedIds)) {
                $queue[] = ['parentRef' => &$rootNode['children'], 'person' => $child, 'depth' => $depth + 1];
            } else {
                if ($onVisitedChild) {
                    $onVisitedChild($child, $person, $depth);
                }
            }
        }
        
        // Process queue level by level
        while (!empty($queue)) {
            $item = array_shift($queue);
            $currentPerson = $item['person'];
            $currentDepth = $item['depth'];
            
            if ($currentDepth > $maxDepth) continue;
            if (in_array($currentPerson->id, $visitedIds)) continue;
            
            $visitedIds[] = $currentPerson->id;
            
            $node = $formatCallback($currentPerson, $currentDepth);
            $node['children'] = [];
            
            // Add to parent's children
            $item['parentRef'][] = $node;
            
            // Get reference to the newly added node's children array
            $lastIndex = count($item['parentRef']) - 1;
            $childrenRef = &$item['parentRef'][$lastIndex]['children'];
            
            // Add this node's children to queue
            if ($currentDepth < $maxDepth) {
                $nextChildren = $currentPerson->children()->orderBy('birth_date', 'asc')->get();
                foreach ($nextChildren as $child) {
                    if (!in_array($child->id, $visitedIds)) {
                        $queue[] = ['parentRef' => &$childrenRef, 'person' => $child, 'depth' => $currentDepth + 1];
                    } else {
                        if ($onVisitedChild) {
                            $onVisitedChild($child, $currentPerson, $currentDepth);
                        }
                    }
                }
            }
        }
        
        // Clean up empty children arrays
        if (empty($rootNode['children'])) {
            unset($rootNode['children']);
        }
        
        return $rootNode;
    }

    /**
     * Build ancestors using Breadth-First Search (queue-based)
     */
    public function buildAncestors($person, int $depth, int $maxDepth, array &$visitedIds, callable $formatCallback): ?array
    {
        if ($depth > $maxDepth) return null;

        $rootNode = $formatCallback($person, $depth);
        $rootNode['children'] = [];
        
        // Use a queue for BFS: [node reference, person model, current depth, processed parents]
        $queue = [];
        $parents = $person->parents;
        $processedParents = [];

        // Add root's parents to queue
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
                
                $coupleNode = $formatCallback($primary, $depth);
                $coupleNode['children'] = [];
                $rootNode['children'][] = $coupleNode;
                
                // Get reference to newly added couple node's children
                $lastIndex = count($rootNode['children']) - 1;
                $coupleChildrenRef = &$rootNode['children'][$lastIndex]['children'];
                
                // Add both primary and secondary parents to queue
                foreach ($primary->parents as $pp) {
                    $queue[] = ['parentRef' => &$coupleChildrenRef, 'person' => $pp, 'depth' => $depth + 1];
                }
                foreach ($secondary->parents as $sp) {
                    $queue[] = ['parentRef' => &$coupleChildrenRef, 'person' => $sp, 'depth' => $depth + 1];
                }

            } else {
                // Single parent
                $processedParents[] = $parent->id;
                $parentNode = $formatCallback($parent, $depth);
                $parentNode['children'] = [];
                $rootNode['children'][] = $parentNode;
                
                // Get reference to newly added parent node's children
                $lastIndex = count($rootNode['children']) - 1;
                $parentChildrenRef = &$rootNode['children'][$lastIndex]['children'];
                
                // Add parent's parents to queue
                foreach ($parent->parents as $grandparent) {
                    $queue[] = ['parentRef' => &$parentChildrenRef, 'person' => $grandparent, 'depth' => $depth + 1];
                }
            }
        }
        
        // Process queue level by level
        while (!empty($queue)) {
            $item = array_shift($queue);
            $currentPerson = $item['person'];
            $currentDepth = $item['depth'];
            
            if ($currentDepth > $maxDepth) continue;
            
            $node = $formatCallback($currentPerson, $currentDepth);
            $node['children'] = [];
            $item['parentRef'][] = $node;
            
            // Get reference to newly added node's children
            $lastIndex = count($item['parentRef']) - 1;
            $nodeChildrenRef = &$item['parentRef'][$lastIndex]['children'];
            
            // Add this person's parents to queue
            if ($currentDepth < $maxDepth) {
                foreach ($currentPerson->parents as $ancestor) {
                    $queue[] = ['parentRef' => &$nodeChildrenRef, 'person' => $ancestor, 'depth' => $currentDepth + 1];
                }
            }
        }
        
        // Clean up empty children arrays recursively
        $this->cleanEmptyChildren($rootNode);
        
        return $rootNode;
    }
    
    /**
     * Recursively remove empty children arrays
     */
    private function cleanEmptyChildren(array &$node): void
    {
        if (isset($node['children'])) {
            if (empty($node['children'])) {
                unset($node['children']);
            } else {
                foreach ($node['children'] as &$child) {
                    $this->cleanEmptyChildren($child);
                }
            }
        }
    }

    /**
     * Build ancestors upward from person using BFS (queue-based)
     */
    public function buildAncestorsUpward($person, int $maxDepth, array &$visitedIds, callable $formatCallback): ?array
    {
        if ($maxDepth <= 0) return null;
        if (in_array($person->id, $visitedIds)) return null;

        $visitedIds[] = $person->id;
        $rootNode = $formatCallback($person, 0);
        $rootNode['children'] = [];
        
        // Use a queue for BFS: [node reference, person model, current depth]
        $queue = [];
        
        // Add root's parents to queue
        $parents = $person->parents;
        foreach ($parents as $parent) {
            if (!in_array($parent->id, $visitedIds)) {
                $queue[] = ['parentRef' => &$rootNode['children'], 'person' => $parent, 'depth' => 1];
            }
        }
        
        // Process queue level by level
        while (!empty($queue)) {
            $item = array_shift($queue);
            $currentPerson = $item['person'];
            $currentDepth = $item['depth'];
            
            if ($currentDepth > $maxDepth) continue;
            if (in_array($currentPerson->id, $visitedIds)) continue;
            
            $visitedIds[] = $currentPerson->id;
            
            $node = $formatCallback($currentPerson, $currentDepth);
            $node['children'] = [];
            $item['parentRef'][] = $node;
            
            // Get reference to newly added node's children array
            $lastIndex = count($item['parentRef']) - 1;
            $childrenRef = &$item['parentRef'][$lastIndex]['children'];
            
            // Add this person's parents to queue
            if ($currentDepth < $maxDepth) {
                foreach ($currentPerson->parents as $ancestor) {
                    if (!in_array($ancestor->id, $visitedIds)) {
                        $queue[] = ['parentRef' => &$childrenRef, 'person' => $ancestor, 'depth' => $currentDepth + 1];
                    }
                }
            }
        }
        
        // Clean up empty children arrays
        $this->cleanEmptyChildren($rootNode);
        
        return $rootNode;
    }
}

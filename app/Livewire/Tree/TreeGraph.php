<?php

namespace App\Livewire\Tree;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Services\TreeTraversal\TreeTraversalStrategy;
use App\Services\TreeTraversal\BfsTraversal;
use App\Services\TreeTraversal\DfsTraversal;

#[Layout('components.layouts.visualizer')]
class TreeGraph extends Component
{
    public FamilyTree $tree;
    public Person $rootPerson;
    public $isWholeTree = false;
    public $originalRootId;
    private $visitedIds = [];
    private ?TreeTraversalStrategy $traversalStrategy = null;
    private $nodeDepths = [];
    private $rootShadowCounts = [];
    private $extraLinks = [];

    public function mount(FamilyTree $tree, $person)
    {
        $this->tree = $tree;
        $this->rootPerson = Person::findOrFail($person);
        $this->originalRootId = $this->rootPerson->id;
        
        if ($this->tree->user_id !== auth()->id() && !$this->tree->is_public) {
            abort(403);
        }

        if ($this->tree->is_public) {
            $this->isWholeTree = true;
        }
    }

    /**
     * Get traversal strategy with lazy initialization
     */
    private function getTraversalStrategy(): TreeTraversalStrategy
    {
        if ($this->traversalStrategy === null) {
            // Initialize traversal strategy (BFS by default)
            // To switch to DFS, use: new DfsTraversal()
            $this->traversalStrategy = new BfsTraversal();
        }
        return $this->traversalStrategy;
    }

    public function toggleWholeTree()
    {
        $this->isWholeTree = !$this->isWholeTree;
        $this->dispatch('graph-updated', data: $this->graphData);
    }

    private function findUltimateAncestor($person)
    {
        // Traverse up the paternal line (or just parents) until we find someone with no parents in the tree
        // For simplicity in this graph, we'll follow the first parent found (usually father)
        $current = $person;
        $visited = [$current->id];

        while ($current->parents->count() > 0) {
            // Prefer father (gender 1)
            $father = $current->parents->firstWhere('gender', \App\Enums\Gender::Male);
            $next = $father ?? $current->parents->first();
            
            if (in_array($next->id, $visited)) break; // Prevent cycles
            
            $current = $next;
            $visited[] = $current->id;
        }

        return $current;
    }

    private function calculateRootGenerations($roots)
    {
        // 1. Map every person to their RootID and Depth relative to that root
        $personMap = []; // [person_id => ['root_id' => int, 'depth' => int]]
        
        foreach ($roots as $root) {
            $this->mapTree($root, $root->id, 0, $personMap);
        }

        // 2. Identify Constraints
        // We want to find relative generation offsets between roots.
        // Gen(RootA) + Offset = Gen(RootB)
        // We can build a graph where nodes are RootIDs and edges are offsets.
        
        $adj = []; // [root_id => [[target_root_id, weight], ...]]
        
        // Initialize adjacency list
        foreach ($roots as $root) {
            $adj[$root->id] = [];
        }

        // Iterate through all people to find cross-tree connections
        foreach ($personMap as $personId => $info) {
            $person = Person::find($personId);
            if (!$person) continue;

            $rootA = $info['root_id'];
            $depthA = $info['depth'];

            // Constraint 1: Spouses should be at the same generation
            foreach ($person->spouses as $spouse) {
                if (isset($personMap[$spouse->id])) {
                    $rootB = $personMap[$spouse->id]['root_id'];
                    $depthB = $personMap[$spouse->id]['depth'];

                    if ($rootA !== $rootB) {
                        // Gen(RootA) + DepthA = Gen(RootB) + DepthB
                        // Gen(RootB) - Gen(RootA) = DepthA - DepthB
                        // Edge A -> B with weight (DepthA - DepthB)
                        $weight = $depthA - $depthB;
                        $adj[$rootA][] = ['target' => $rootB, 'weight' => $weight];
                        $adj[$rootB][] = ['target' => $rootA, 'weight' => -$weight];
                    }
                }
            }

            // Constraint 2: Parent -> Child
            // If child is in another tree (which happens if child is a root of another tree or part of another tree)
            // But in our logic, if child is in another tree, it's usually because it's a root or connected to a root.
            // Actually, if we are traversing roots, we might not see the child in the traversal if it's in another tree?
            // Wait, mapTree traverses descendants. So if a child is in another tree, it might be visited twice?
            // No, visitedIds prevents re-visiting.
            // But we need to handle the case where a person in Tree A is a parent of a person in Tree B.
            
            // Let's check children who are NOT in the same tree (based on our map)
            // Actually, mapTree only traverses what buildDescendants traverses.
            // If buildDescendants stops at visited nodes, we need to check those boundaries.
            
            foreach ($person->children as $child) {
                if (isset($personMap[$child->id])) {
                    $rootB = $personMap[$child->id]['root_id'];
                    $depthB = $personMap[$child->id]['depth'];
                    
                    if ($rootA !== $rootB) {
                        // Gen(RootA) + DepthA = Gen(RootB) + DepthB - 1 (Parent is 1 gen above child)
                        // Gen(RootB) - Gen(RootA) = DepthA - DepthB + 1
                        $weight = $depthA - $depthB + 1;
                        $adj[$rootA][] = ['target' => $rootB, 'weight' => $weight];
                        $adj[$rootB][] = ['target' => $rootA, 'weight' => -$weight];
                    }
                }
            }
        }

        // 3. Solve for Generations (BFS/DFS on the root graph)
        $generations = []; // [root_id => generation]
        foreach ($roots as $root) {
            if (!isset($generations[$root->id])) {
                $this->solveGenerations($root->id, 0, $adj, $generations);
            }
        }

        // Normalize so min generation is 0
        if (empty($generations)) return [];
        
        $minGen = min($generations);
        foreach ($generations as $id => $gen) {
            $generations[$id] = $gen - $minGen;
        }

        return $generations;
    }

    private function mapTree($person, $rootId, $depth, &$map)
    {
        if (isset($map[$person->id])) return;
        $map[$person->id] = ['root_id' => $rootId, 'depth' => $depth];

        foreach ($person->children as $child) {
            // Only traverse if not already mapped (avoids cycles and re-entry)
            // But wait, if we have multiple roots, we want to map each disjoint tree.
            // If a child is already mapped, it belongs to another tree (or this one visited earlier).
            // We stop here. The connection will be found in step 2.
            if (!isset($map[$child->id])) {
                $this->mapTree($child, $rootId, $depth + 1, $map);
            }
        }
    }

    private function solveGenerations($u, $currentGen, $adj, &$generations)
    {
        $generations[$u] = $currentGen;
        
        if (isset($adj[$u])) {
            foreach ($adj[$u] as $edge) {
                $v = $edge['target'];
                $weight = $edge['weight'];
                
                if (!isset($generations[$v])) {
                    $this->solveGenerations($v, $currentGen + $weight, $adj, $generations);
                }
            }
        }
    }

    public function getGraphDataProperty()
    {
        $this->visitedIds = [];
        $this->nodeDepths = [];
        $this->rootShadowCounts = [];
        $this->extraLinks = [];

        if ($this->isWholeTree) {
            // Forest View: Find all roots
            $roots = Person::where('family_tree_id', $this->tree->id)
                ->whereDoesntHave('parents')
                ->with(['spouses.parents', 'children'])
                ->get();

            // Filter out roots who have a spouse that HAS parents (they belong to another tree)
            $roots = $roots->filter(function($root) {
                foreach ($root->spouses as $spouse) {
                    if ($spouse->parents->count() > 0) {
                        return false;
                    }
                }
                return true;
            });
                
            // Create a Virtual Root
            $virtualRoot = [
                'name' => 'Family Tree',
                'id' => 'virtual_root',
                'gender' => null,
                'photo' => asset('images/defaults/tree_icon.svg'),
                'is_virtual' => true,
                'children' => [],
            ];
            
            // Sort roots by size (descendants count) to process larger trees first?
            // Or just process them. If we process a small tree that connects to a large tree later,
            // we might miss the depth info if the large tree hasn't been processed.
            // Ideally, we should process the "main" tree first.
            // For now, let's assume the order is roughly correct or we handle it.
            // Actually, if we encounter a visited child, that child MUST have been processed already.
            // So we rely on the order.
            
            // Calculate generations for alignment
            $rootGenerations = $this->calculateRootGenerations($roots);
            $this->rootShadowCounts = $rootGenerations;

            foreach ($roots as $root) {
                if (in_array($root->id, $this->visitedIds)) continue;

                // Capture current root ID for the callback
                $currentRootId = $root->id;

                $rootNode = $this->buildDescendants($root, 0, 50, function($child, $parent, $depth) use ($currentRootId) {
                     // Add extra link if we encounter a visited child
                     $this->extraLinks[] = [
                        'source' => $parent->id,
                        'target' => $child->id,
                        'type' => 'parent-child'
                    ];
                });

                if ($rootNode) {
                    // Inject shadow nodes if needed
                    if (isset($this->rootShadowCounts[$root->id]) && $this->rootShadowCounts[$root->id] > 0) {
                        $count = $this->rootShadowCounts[$root->id];
                        $currentNode = $rootNode;
                        
                        for ($i = 0; $i < $count; $i++) {
                            $shadow = [
                                'name' => 'Shadow',
                                'id' => 'shadow_' . uniqid() . '_' . $i,
                                'gender' => null,
                                'photo' => null,
                                'is_virtual' => true,
                                'children' => [$currentNode],
                                'spouses' => [],
                            ];
                            $currentNode = $shadow;
                        }
                        $virtualRoot['children'][] = $currentNode;
                    } else {
                        $virtualRoot['children'][] = $rootNode;
                    }
                }
            }
            
            return [
                'ancestors' => null,
                'descendants' => $virtualRoot,
                'siblings' => [],
                'extra_links' => $this->extraLinks,
            ];
        }

        $ancestors = $this->buildAncestors($this->rootPerson);

        // Allow root to be processed again for descendants
        if (($key = array_search($this->rootPerson->id, $this->visitedIds)) !== false) {
            unset($this->visitedIds[$key]);
        }

        return [
            'ancestors' => $ancestors,
            'descendants' => $this->buildDescendants($this->rootPerson, 0, 10),
            'siblings' => $this->rootPerson->siblings()->map(fn($s) => $this->formatNode($s))->values()->toArray(),
        ];
    }

    private function formatNode($person, $depth = 0)
    {
        // Record absolute depth of this node
        // Note: For secondary trees, this depth is relative to their root.
        // We'll adjust it later using rootOffsets.
        $this->nodeDepths[$person->id] = $depth;

        return [
            'name' => $person->full_name,
            'id' => $person->id,
            'gender' => $person->gender?->value,
            'photo' => $person->default_photo_url,
            'birth_date' => $person->birth_date?->timestamp,
            'spouses' => $person->spouses->map(function($s) {
                $this->visitedIds[] = $s->id;
                return [
                    'name' => $s->full_name,
                    'id' => $s->id,
                    'gender' => $s->gender?->value,
                    'photo' => $s->default_photo_url,
                ];
            })->toArray(),
        ];
    }

    private function buildAncestors($person, $depth = 0, $maxDepth = 5)
    {
        return $this->getTraversalStrategy()->buildAncestors(
            $person,
            $depth,
            $maxDepth,
            $this->visitedIds,
            fn($p, $d) => $this->formatNode($p, $d)
        );
    }

    private function buildDescendants($person, $depth = 0, $maxDepth = 10, ?callable $onVisitedChild = null)
    {
        return $this->getTraversalStrategy()->buildDescendants(
            $person,
            $depth,
            $maxDepth,
            $this->visitedIds,
            fn($p, $d) => $this->formatNode($p, $d),
            $onVisitedChild
        );
    }


    public function render()
    {
        return view('livewire.tree.tree-graph');
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Tree\TreeIndex;
use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_tree_index_page_renders_correctly()
    {
        $user = User::factory()->create();
        $tree = FamilyTree::create([
            'user_id' => $user->id,
            'name' => 'My Family Tree',
            'description' => 'Description',
            'slug' => 'my-family-tree',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('tree.index'));

        $response->assertOk();
        $response->assertSee('My Family Tree');

        Livewire::test(TreeIndex::class)
            ->assertSee('My Family Tree');
    }

    public function test_user_can_create_tree()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(TreeIndex::class)
            ->set('name', 'New Tree')
            ->set('description', 'New Description')
            ->call('save')
            ->assertDispatched('tree-created');

        $this->assertDatabaseHas('family_trees', [
            'name' => 'New Tree',
            'description' => 'New Description',
            'user_id' => $user->id,
        ]);
    }
}

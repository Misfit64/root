<?php

namespace Tests\Feature;

use App\Livewire\Person\PersonShow;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

use App\Enums\Gender;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_show_page_renders_correctly()
    {
        $user = User::factory()->create();
        $tree = FamilyTree::create([
            'user_id' => $user->id,
            'name' => 'Test Tree',
            'description' => 'Test Description',
            'slug' => 'test-tree',
        ]);
        $person = Person::create([
            'family_tree_id' => $tree->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => Gender::Male,
            'date_of_birth' => '1990-01-01',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('person.show', ['tree' => $tree, 'person' => $person]));

        $response->assertOk();
        $response->assertSee('John Doe');

        Livewire::test(PersonShow::class, ['tree' => $tree, 'person' => $person])
            ->assertSee('John Doe');
    }
}

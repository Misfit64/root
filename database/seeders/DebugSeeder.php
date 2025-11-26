<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Enums\Gender;

class DebugSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'debug@example.com'],
            ['name' => 'Debug User', 'password' => bcrypt('password')]
        );

        $tree = FamilyTree::create([
            'name' => 'Test Tree',
            'slug' => 'test-tree',
            'user_id' => $user->id,
        ]);

        Person::create([
            'family_tree_id' => $tree->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => Gender::Male,
        ]);

        Person::create([
            'family_tree_id' => $tree->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => Gender::Female,
        ]);
    }
}

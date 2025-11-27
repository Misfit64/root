<?php

namespace Database\Seeders;

use App\Models\FamilyTree;
use App\Models\Person;
use App\Models\User;
use App\Enums\Gender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GameOfThronesSeeder extends Seeder
{
    public function run(): void
    {
        // Create Demo User
        $user = User::firstOrCreate(
            ['email' => 'demo@familytree.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        // Delete existing Game of Thrones tree to ensure fresh seed
        $existingTree = FamilyTree::where('slug', 'game-of-thrones')->first();
        if ($existingTree) {
            $existingTree->delete();
        }

        // Create Public Tree
        $tree = FamilyTree::create([
            'user_id' => $user->id,
            'name' => 'Game of Thrones',
            'slug' => 'game-of-thrones',
            'description' => 'A demo tree showing the major houses of Westeros.',
            'is_public' => true,
        ]);

        // --- STARKS ---
        $rickard = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Rickard', 'last_name' => 'Stark', 'gender' => Gender::Male]);
        $lyarra = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Lyarra', 'last_name' => 'Stark', 'gender' => Gender::Female]);
        
        // Link Rickard and Lyarra
        // Assuming we have a way to link spouses, let's use the relationship table directly or helper
        // For simplicity in seeder, we can just create them. If we need to link them properly as spouses:
        // We'll skip complex spouse logic for now and just focus on parent-child for the main tree structure
        // But the graph relies on spouses for alignment.
        // Let's manually create spouse entries if needed, or just rely on parent links.
        // Actually, the graph uses `spouses` relationship. We should populate `relationships` table.
        
        $this->addSpouse($rickard, $lyarra);

        $brandon = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Brandon', 'last_name' => 'Stark', 'gender' => Gender::Male]);
        $eddard = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Eddard', 'last_name' => 'Stark', 'gender' => Gender::Male]);
        $lyanna = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Lyanna', 'last_name' => 'Stark', 'gender' => Gender::Female]);
        $benjen = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Benjen', 'last_name' => 'Stark', 'gender' => Gender::Male]);

        $this->addChildren($rickard, $lyarra, [$brandon, $eddard, $lyanna, $benjen]);

        $catelyn = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Catelyn', 'last_name' => 'Tully', 'gender' => Gender::Female]);
        $this->addSpouse($eddard, $catelyn);

        $robb = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Robb', 'last_name' => 'Stark', 'gender' => Gender::Male]);
        $sansa = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Sansa', 'last_name' => 'Stark', 'gender' => Gender::Female]);
        $arya = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Arya', 'last_name' => 'Stark', 'gender' => Gender::Female]);
        $bran = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Bran', 'last_name' => 'Stark', 'gender' => Gender::Male]);
        $rickon = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Rickon', 'last_name' => 'Stark', 'gender' => Gender::Male]);
        
        $this->addChildren($eddard, $catelyn, [$robb, $sansa, $arya, $bran, $rickon]);

        $jonSnow = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Jon', 'last_name' => 'Snow', 'gender' => Gender::Male]);

        // --- TARGARYENS ---
        $aerys = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Aerys II', 'last_name' => 'Targaryen', 'gender' => Gender::Male]);
        $rhaella = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Rhaella', 'last_name' => 'Targaryen', 'gender' => Gender::Female]);
        $this->addSpouse($aerys, $rhaella);

        $rhaegar = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Rhaegar', 'last_name' => 'Targaryen', 'gender' => Gender::Male]);
        $viserys = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Viserys', 'last_name' => 'Targaryen', 'gender' => Gender::Male]);
        $daenerys = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Daenerys', 'last_name' => 'Targaryen', 'gender' => Gender::Female]);

        $this->addChildren($aerys, $rhaella, [$rhaegar, $viserys, $daenerys]);

        // Rhaegar + Lyanna = Jon Snow
        $this->addSpouse($rhaegar, $lyanna);
        $this->addChildren($rhaegar, $lyanna, [$jonSnow]);

        // Rhaegar + Elia Martell
        $elia = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Elia', 'last_name' => 'Martell', 'gender' => Gender::Female]);
        $this->addSpouse($rhaegar, $elia);
        $rhaenys = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Rhaenys', 'last_name' => 'Targaryen', 'gender' => Gender::Female]);
        $aegon = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Aegon', 'last_name' => 'Targaryen', 'gender' => Gender::Male]);
        $this->addChildren($rhaegar, $elia, [$rhaenys, $aegon]);


        // --- LANNISTERS ---
        $tywin = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Tywin', 'last_name' => 'Lannister', 'gender' => Gender::Male]);
        $joanna = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Joanna', 'last_name' => 'Lannister', 'gender' => Gender::Female]);
        $this->addSpouse($tywin, $joanna);

        $cersei = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Cersei', 'last_name' => 'Lannister', 'gender' => Gender::Female]);
        $jaime = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Jaime', 'last_name' => 'Lannister', 'gender' => Gender::Male]);
        $tyrion = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Tyrion', 'last_name' => 'Lannister', 'gender' => Gender::Male]);

        $this->addChildren($tywin, $joanna, [$cersei, $jaime, $tyrion]);

        // --- BARATHEONS ---
        $robert = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Robert', 'last_name' => 'Baratheon', 'gender' => Gender::Male]);
        $this->addSpouse($robert, $cersei);

        $joffrey = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Joffrey', 'last_name' => 'Baratheon', 'gender' => Gender::Male]);
        $myrcella = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Myrcella', 'last_name' => 'Baratheon', 'gender' => Gender::Female]);
        $tommen = Person::create(['family_tree_id' => $tree->id, 'first_name' => 'Tommen', 'last_name' => 'Baratheon', 'gender' => Gender::Male]);

        // Jamie is the real father, but for the tree usually Robert is shown as father or Jamie?
        // Let's link them to Cersei and Jaime for truth, or Robert for public face?
        // Let's go with the show's truth: Jaime and Cersei.
        // But legally Robert.
        // Let's link to Jaime and Cersei.
        $this->addSpouse($jaime, $cersei);
        $this->addChildren($jaime, $cersei, [$joffrey, $myrcella, $tommen]);
    }

    private function addSpouse($p1, $p2)
    {
        // Add relationship both ways
        \DB::table('relationships')->insert([
            ['family_tree_id' => $p1->family_tree_id, 'person_id' => $p1->id, 'relative_id' => $p2->id, 'relationship_type' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['family_tree_id' => $p2->family_tree_id, 'person_id' => $p2->id, 'relative_id' => $p1->id, 'relationship_type' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function addChildren($father, $mother, $children)
    {
        foreach ($children as $child) {
            // Link to Father
            \DB::table('relationships')->insert([
                ['family_tree_id' => $father->family_tree_id, 'person_id' => $father->id, 'relative_id' => $child->id, 'relationship_type' => 2, 'created_at' => now(), 'updated_at' => now()], // Father -> Child (Type 2 = Child)
                ['family_tree_id' => $child->family_tree_id, 'person_id' => $child->id, 'relative_id' => $father->id, 'relationship_type' => 1, 'created_at' => now(), 'updated_at' => now()], // Child -> Father (Type 1 = Parent)
            ]);
            
            // Link to Mother
            \DB::table('relationships')->insert([
                ['family_tree_id' => $mother->family_tree_id, 'person_id' => $mother->id, 'relative_id' => $child->id, 'relationship_type' => 2, 'created_at' => now(), 'updated_at' => now()], // Mother -> Child
                ['family_tree_id' => $child->family_tree_id, 'person_id' => $child->id, 'relative_id' => $mother->id, 'relationship_type' => 1, 'created_at' => now(), 'updated_at' => now()], // Child -> Mother
            ]);
        }
    }
}

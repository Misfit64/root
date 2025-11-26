<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_tree_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->foreignId('relative_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('relationship_type');
            $table->unsignedTinyInteger('relationship_subtype')->default(RelationshipSubType::Unknown->value);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->check('relationship_type in (1,2,3)');
            $table->check('relationship_subtype in (1,2,3,4)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};

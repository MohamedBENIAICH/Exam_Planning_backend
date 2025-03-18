<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('acces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composant_id')->constrained()->onDelete('cascade'); // Clé étrangère vers composants
            $table->foreignId('role_id')->constrained()->onDelete('cascade'); // Clé étrangère vers roles
            $table->foreignId('type_permission_id')->constrained('type_permissions')->onDelete('cascade'); // Clé étrangère vers type_permissions
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acces');
    }
};

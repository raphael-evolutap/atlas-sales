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
        Schema::table('venda_items', function (Blueprint $table) {
            $table->foreignId('cidade_id')->nullable()->after('produto_id')->constrained('cidades')->nullOnDelete();
        });

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->foreignId('cidade_id')->nullable()->after('produto_id')->constrained('cidades')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venda_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cidade_id');
        });

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cidade_id');
        });
    }
};

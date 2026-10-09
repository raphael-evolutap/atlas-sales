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
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('comissao_pct', 5, 2)->default(0)->after('preco_venda_int');
        });

        // Snapshot da comissão no momento da venda, como o preço.
        Schema::table('venda_items', function (Blueprint $table) {
            $table->decimal('comissao_pct', 5, 2)->default(0)->after('subtotal_int');
            $table->unsignedBigInteger('comissao_int')->default(0)->after('comissao_pct');
        });

        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn('comissao_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendedores', function (Blueprint $table) {
            $table->unsignedInteger('comissao_pct')->default(0)->after('telefone');
        });

        Schema::table('venda_items', function (Blueprint $table) {
            $table->dropColumn(['comissao_pct', 'comissao_int']);
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('comissao_pct');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Itens de venda e movimentações passam a apontar para um lote. Os registros
     * existentes, anteriores aos lotes, vão para o lote "INICIAL" do produto.
     */
    public function up(): void
    {
        foreach (['venda_items', 'movimentacoes'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->foreignId('lote_id')->nullable()->after('cidade_id');
            });

            $this->preencherComLoteInicial($tabela);

            Schema::table($tabela, function (Blueprint $table) {
                $table->foreignId('lote_id')->nullable(false)->change();
                $table->foreign('lote_id')->references('id')->on('produto_lotes')->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venda_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lote_id');
        });

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lote_id');
        });
    }

    private function preencherComLoteInicial(string $tabela): void
    {
        $agora = now();

        DB::table($tabela)
            ->whereNull('lote_id')
            ->distinct()
            ->pluck('produto_id')
            ->each(fn (int $produtoId) => DB::table('produto_lotes')->insertOrIgnore([
                'produto_id' => $produtoId,
                'numero' => 'INICIAL',
                'created_at' => $agora,
                'updated_at' => $agora,
            ]));

        DB::table($tabela)
            ->whereNull('lote_id')
            ->update(['lote_id' => DB::raw(
                "(select produto_lotes.id from produto_lotes where produto_lotes.produto_id = {$tabela}.produto_id and produto_lotes.numero = 'INICIAL')"
            )]);
    }
};

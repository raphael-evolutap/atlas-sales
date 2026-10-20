<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Saldo por lote dentro do estoque de uma cidade. O saldo existente,
     * que ainda não tem lote, vai para um lote "INICIAL" do produto.
     */
    public function up(): void
    {
        Schema::create('produto_estoque_lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_estoque_id')->constrained('produto_estoques')->cascadeOnDelete();
            $table->foreignId('lote_id')->constrained('produto_lotes')->restrictOnDelete();
            $table->integer('quantidade')->default(0);
            $table->timestamps();
            $table->unique(['produto_estoque_id', 'lote_id']);
        });

        DB::table('produto_estoques')
            ->where('quantidade', '>', 0)
            ->orderBy('id')
            ->each(function (object $estoque): void {
                $agora = now();

                DB::table('produto_lotes')->insertOrIgnore([
                    'produto_id' => $estoque->produto_id,
                    'numero' => 'INICIAL',
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);

                DB::table('produto_estoque_lotes')->insert([
                    'produto_estoque_id' => $estoque->id,
                    'lote_id' => DB::table('produto_lotes')
                        ->where('produto_id', $estoque->produto_id)
                        ->where('numero', 'INICIAL')
                        ->value('id'),
                    'quantidade' => $estoque->quantidade,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_estoque_lotes');
    }
};

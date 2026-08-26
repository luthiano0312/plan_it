<?php

namespace Tests\Unit;

use App\Enums\ItemStatus;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_pode_ter_pai_e_filhos(): void
    {
        $pai = Item::factory()->create();
        $filho = Item::factory()->create(['parent_id' => $pai->id]);

        $this->assertTrue($pai->children->contains($filho));
        $this->assertEquals($pai->id, $filho->parent->id);
        $this->assertFalse($pai->isLeaf());
        $this->assertTrue($filho->isLeaf());
    }

    public function test_descendant_ids_retorna_todos_os_niveis(): void
    {
        $avo = Item::factory()->create();
        $pai = Item::factory()->create(['parent_id' => $avo->id]);
        $filho = Item::factory()->create(['parent_id' => $pai->id]);

        $this->assertEqualsCanonicalizing([$pai->id, $filho->id], $avo->descendantIds()->all());
    }

    public function test_actionable_exclui_concluidos_e_projetos_com_filhos_pendentes(): void
    {
        $folha = Item::factory()->create();
        $concluido = Item::factory()->concluded()->create();
        $projeto = Item::factory()->create();
        Item::factory()->create(['parent_id' => $projeto->id]); // passo pendente

        $ids = Item::actionable()->pluck('id');
        $this->assertTrue($ids->contains($folha->id));
        $this->assertFalse($ids->contains($concluido->id));
        $this->assertFalse($ids->contains($projeto->id));
    }

    public function test_deletar_projeto_apaga_descendentes_em_cascade(): void
    {
        $projeto = Item::factory()->create();
        $passo = Item::factory()->create(['parent_id' => $projeto->id]);

        $projeto->delete();

        $this->assertDatabaseMissing('items', ['id' => $passo->id]);
    }
}

<?php

namespace Tests\Unit;

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Services\ItemTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemTransitionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function transitions(): ItemTransitionService
    {
        return new ItemTransitionService;
    }

    public function test_concluir_ultimo_filho_conclui_projeto_automaticamente(): void
    {
        $projeto = Item::factory()->create();
        Item::factory()->concluded()->create(['parent_id' => $projeto->id]);
        $ultimoPasso = Item::factory()->create(['parent_id' => $projeto->id]);

        $resultado = $this->transitions()->complete($ultimoPasso);

        $projeto->refresh();
        $this->assertEquals(ItemStatus::Concluido, $projeto->status);
        $this->assertNotNull($projeto->completed_at);
        $this->assertEquals(ItemStatus::Concluido, $resultado->status);
    }

    public function test_projeto_nao_conclui_se_ainda_houver_filho_pendente(): void
    {
        $projeto = Item::factory()->create();
        $passoA = Item::factory()->create(['parent_id' => $projeto->id]);
        $passoB = Item::factory()->create(['parent_id' => $projeto->id]);

        $this->transitions()->complete($passoA);

        $this->assertEquals(ItemStatus::Pendente, $projeto->refresh()->status);
    }

    public function test_conclusao_propaga_por_dois_niveis(): void
    {
        $avo = Item::factory()->create();
        $pai = Item::factory()->create(['parent_id' => $avo->id]);
        $filhoUnico = Item::factory()->create(['parent_id' => $pai->id]);

        $this->transitions()->complete($filhoUnico);

        $this->assertEquals(ItemStatus::Concluido, $pai->refresh()->status);
        $this->assertEquals(ItemStatus::Concluido, $avo->refresh()->status);
    }

    public function test_concluir_projeto_manualmente_e_permitido_com_filhos_pendentes(): void
    {
        $projeto = Item::factory()->create();
        $passo = Item::factory()->create(['parent_id' => $projeto->id]);

        $this->transitions()->complete($projeto);

        $this->assertEquals(ItemStatus::Concluido, $projeto->refresh()->status);
        $this->assertEquals(ItemStatus::Pendente, $passo->refresh()->status);
    }

    public function test_mark_in_progress_so_afeta_item_pendente(): void
    {
        $pendente = Item::factory()->create();
        $andamento = Item::factory()->create(['status' => ItemStatus::EmAndamento]);
        $concluido = Item::factory()->concluded()->create();

        $this->transitions()->markInProgressIfNeeded($pendente);
        $this->transitions()->markInProgressIfNeeded($andamento);
        $this->transitions()->markInProgressIfNeeded($concluido);

        $this->assertEquals(ItemStatus::EmAndamento, $pendente->refresh()->status);
        $this->assertEquals(ItemStatus::EmAndamento, $andamento->refresh()->status);
        $this->assertEquals(ItemStatus::Concluido, $concluido->refresh()->status);
    }
}

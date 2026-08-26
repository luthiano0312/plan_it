<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filtra_por_status_e_lista_campos_do_recurso(): void
    {
        Item::factory()->create(['title' => 'A']);
        Item::factory()->concluded()->create(['title' => 'B']);

        $resposta = $this->getJson('/api/items?status=pendente');

        $resposta->assertOk()->assertJsonCount(1, 'data');
        $resposta->assertJsonFragment(['title' => 'A', 'status' => 'pendente']);
    }

    public function test_store_cria_item_com_defaults(): void
    {
        $resposta = $this->postJson('/api/items', ['title' => 'Novo item']);

        $resposta->assertCreated();
        $this->assertDatabaseHas('items', ['title' => 'Novo item', 'status' => 'pendente', 'effort' => 3]);
    }

    public function test_store_valida_title_obrigatorio(): void
    {
        $this->postJson('/api/items', [])->assertUnprocessable()->assertJsonValidationErrors('title');
    }

    public function test_show_retorna_children_time_sessions_e_total_seconds(): void
    {
        $projeto = Item::factory()->create();
        $passo = Item::factory()->create(['parent_id' => $projeto->id]);
        TimeEntry::factory()->create(['item_id' => $passo->id, 'started_at' => now()->subSeconds(60), 'ended_at' => now()]);

        $resposta = $this->getJson("/api/items/{$projeto->id}");

        $resposta->assertOk()
            ->assertJsonPath('children.0.id', $passo->id)
            ->assertJsonPath('total_seconds', fn ($v) => abs($v - 60) <= 2)
            ->assertJsonCount(1, 'time_sessions');
    }

    public function test_update_marcando_concluido_propaga_pro_projeto(): void
    {
        $projeto = Item::factory()->create();
        $passo = Item::factory()->create(['parent_id' => $projeto->id]);

        $this->patchJson("/api/items/{$passo->id}", ['status' => 'concluido'])->assertOk();

        $this->assertEquals(ItemStatus::Concluido, $projeto->refresh()->status);
    }

    public function test_update_saindo_de_concluido_zera_completed_at(): void
    {
        $item = Item::factory()->concluded()->create();

        $this->patchJson("/api/items/{$item->id}", ['status' => 'pendente'])->assertOk();

        $this->assertNull($item->refresh()->completed_at);
        $this->assertEquals(ItemStatus::Pendente, $item->status);
    }

    public function test_destroy_remove_item_e_filhos(): void
    {
        $projeto = Item::factory()->create();
        $passo = Item::factory()->create(['parent_id' => $projeto->id]);

        $this->deleteJson("/api/items/{$projeto->id}")->assertNoContent();

        $this->assertDatabaseMissing('items', ['id' => $passo->id]);
    }
}

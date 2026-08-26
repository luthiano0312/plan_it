<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NowAndTimerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_now_retorna_shortlist_priorizada_com_is_running(): void
    {
        $fixado = Item::factory()->create(['title' => 'Fixado', 'manual_priority' => 1]);
        $rodando = Item::factory()->create(['title' => 'Rodando', 'due_date' => now()->subDay()]);
        TimeEntry::factory()->create(['item_id' => $rodando->id, 'ended_at' => null]);

        $resposta = $this->getJson('/api/now');

        $resposta->assertOk();
        $resposta->assertJsonFragment(['title' => 'Fixado', 'is_running' => false]);
        $resposta->assertJsonFragment(['title' => 'Rodando', 'is_running' => true]);
    }

    public function test_timer_start_via_api_fecha_sessao_de_outro_item(): void
    {
        $a = Item::factory()->create();
        $b = Item::factory()->create();
        $this->postJson("/api/items/{$a->id}/timer/start")->assertCreated();

        $this->postJson("/api/items/{$b->id}/timer/start")->assertCreated();

        $this->assertNotNull($a->timeEntries()->first()->refresh()->ended_at);
        $aberta = TimeEntry::whereNull('ended_at')->sole();
        $this->assertEquals($b->id, $aberta->item_id);
    }

    public function test_timer_stop_para_a_sessao_atual(): void
    {
        $item = Item::factory()->create();
        $this->postJson("/api/items/{$item->id}/timer/start");

        $this->postJson('/api/timer/stop')->assertOk();

        $this->assertNull(TimeEntry::whereNull('ended_at')->first());
    }

    public function test_timer_current_sem_sessao_retorna_null(): void
    {
        $this->getJson('/api/timer/current')->assertOk()->assertJson(['data' => null]);
    }
}

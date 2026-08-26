<?php

namespace Tests\Unit;

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Models\TimeEntry;
use App\Services\ItemTransitionService;
use App\Services\TimerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimerServiceTest extends TestCase
{
    use RefreshDatabase;

    private function timer(): TimerService
    {
        return new TimerService(new ItemTransitionService);
    }

    public function test_iniciar_timer_cria_sessao_aberta_e_marca_em_andamento(): void
    {
        $item = Item::factory()->create(); // pendente

        $entry = $this->timer()->start($item);

        $this->assertNull($entry->ended_at);
        $this->assertEquals(ItemStatus::EmAndamento, $item->refresh()->status);
    }

    public function test_iniciar_em_outro_item_fecha_a_sessao_anterior(): void
    {
        $a = Item::factory()->create();
        $b = Item::factory()->create();
        $this->timer()->start($a);

        $this->timer()->start($b);

        $this->assertNotNull($a->timeEntries()->first()->refresh()->ended_at);
        $aberta = $this->timer()->current();
        $this->assertNotNull($aberta);
        $this->assertEquals($b->id, $aberta->item_id);
    }

    public function test_iniciar_no_mesmo_item_eh_idempotente(): void
    {
        $a = Item::factory()->create();
        $primeira = $this->timer()->start($a);

        $segunda = $this->timer()->start($a);

        $this->assertEquals($primeira->id, $segunda->id);
        $this->assertCount(1, $a->timeEntries()->get());
    }

    public function test_item_ja_concluido_nao_muda_de_status_ao_iniciar(): void
    {
        $item = Item::factory()->concluded()->create();

        $this->timer()->start($item);

        $this->assertEquals(ItemStatus::Concluido, $item->refresh()->status);
    }

    public function test_total_seconds_soma_todos_os_descendentes(): void
    {
        $projeto = Item::factory()->create();
        $passo = Item::factory()->create(['parent_id' => $projeto->id]);
        $neto = Item::factory()->create(['parent_id' => $passo->id]);
        TimeEntry::factory()->create(['item_id' => $neto->id, 'started_at' => now()->subMinutes(10), 'ended_at' => now()->subMinutes(4)]);  // 360 s
        TimeEntry::factory()->create(['item_id' => $passo->id, 'started_at' => now()->subMinutes(3), 'ended_at' => now()->subMinute()]);   // 120 s

        $this->assertEquals(480, $this->timer()->totalSeconds($projeto));
    }

    public function test_total_seconds_conta_sessao_aberta_ate_agora(): void
    {
        $item = Item::factory()->create();
        TimeEntry::factory()->create(['item_id' => $item->id, 'started_at' => now()->subMinutes(5), 'ended_at' => null]);

        $total = $this->timer()->totalSeconds($item);
        $this->assertGreaterThanOrEqual(290, $total);
        $this->assertLessThan(320, $total);
    }

    public function test_stop_sem_sessao_aberta_retorna_null(): void
    {
        $this->assertNull($this->timer()->stopCurrent());
    }
}

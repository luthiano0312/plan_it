<?php

namespace Tests\Unit;

use App\Models\Item;
use App\Services\PriorityScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PriorityScorerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('planit.priority', [
            'due_weight' => 3.0,
            'ease_weight' => 1.0,
            'urgency_max' => 10.0,
            'urgency_horizon_days' => 14,
            'urgency_no_due' => 1.0,
        ]);
    }

    private function scorer(): PriorityScorer
    {
        return new PriorityScorer;
    }

    public function test_itens_com_manual_priority_vao_primeiro_em_ordem_ascendente(): void
    {
        Item::factory()->create(['title' => 'auto']);
        Item::factory()->create(['title' => 'manual 1', 'manual_priority' => 1]);
        Item::factory()->create(['title' => 'manual 2', 'manual_priority' => 2]);

        $titulos = $this->scorer()->shortlist()->pluck('title');

        $this->assertEquals(['manual 1', 'manual 2', 'auto'], $titulos->all());
    }

    public function test_urgencia_satura_para_itens_vencidos(): void
    {
        // effort fixo para o termo de facilidade não mascarar a comparação de urgência
        $ontem = Item::factory()->create(['due_date' => Carbon::yesterday(), 'effort' => 3]);
        $haMes = Item::factory()->create(['due_date' => Carbon::today()->subDays(30), 'effort' => 3]);

        $this->assertSame($this->scorer()->score($ontem), $this->scorer()->score($haMes));
    }

    public function test_urgencia_cai_linearmente_e_zera_no_limite_do_horizonte(): void
    {
        $amanha = Item::factory()->create(['due_date' => Carbon::tomorrow()]);
        $noLimite = Item::factory()->create(['due_date' => Carbon::today()->addDays(14)]);

        $this->assertGreaterThan($this->scorer()->score($noLimite), $this->scorer()->score($amanha));
        $this->assertEquals(0.0, $this->scorer()->urgency(Carbon::today()->addDays(14)));
    }

    public function test_sem_prazo_tem_urgencia_fixa_baixa_nao_zero(): void
    {
        $this->assertEquals(1.0, $this->scorer()->urgency(null));
    }

    public function test_menor_esforco_ganha_empate_de_urgencia(): void
    {
        $facil = Item::factory()->create(['due_date' => Carbon::tomorrow(), 'effort' => 1]);
        $dificil = Item::factory()->create(['due_date' => Carbon::tomorrow(), 'effort' => 5]);

        $this->assertGreaterThan($this->scorer()->score($dificil), $this->scorer()->score($facil));
    }

    public function test_empate_de_manual_priority_desempata_pelo_score(): void
    {
        // inserção proposital: menor score primeiro, para a ordem estável do
        // banco não mascarar a ausência de desempate
        $dificil = Item::factory()->create(['title' => 'dificil', 'manual_priority' => 1, 'effort' => 5]);
        $facil = Item::factory()->create(['title' => 'facil', 'manual_priority' => 1, 'effort' => 1]);

        $titulos = $this->scorer()->shortlist()->pluck('title');

        $this->assertEquals(['facil', 'dificil'], $titulos->all());
    }

    public function test_shortlist_exclui_projeto_com_passos_pendentes_e_limita_tamanho(): void
    {
        $projeto = Item::factory()->create();
        Item::factory()->times(7)->create(['parent_id' => $projeto->id]);

        $lista = $this->scorer()->shortlist(3);

        $this->assertCount(3, $lista);
        $this->assertFalse($lista->contains(fn ($i) => $i->id === $projeto->id));
    }
}

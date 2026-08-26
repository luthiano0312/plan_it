<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $projetoEscola = Item::factory()->create(['title' => 'Trabalho de cálculo', 'description' => 'Entregar até fim do mês', 'due_date' => now()->addDays(3)]);
        Item::factory()->create(['title' => 'Resolver lista 1', 'parent_id' => $projetoEscola->id, 'due_date' => now()->addDay(), 'effort' => 2]);
        Item::factory()->create(['title' => 'Revisar teoria', 'parent_id' => $projetoEscola->id, 'effort' => 5]);
        Item::factory()->concluded()->create(['title' => 'Separar material', 'parent_id' => $projetoEscola->id]);

        Item::factory()->create(['title' => 'Pagar conta de luz', 'due_date' => now()->subDay(), 'effort' => 1, 'manual_priority' => 1]);
        Item::factory()->create(['title' => 'Organizar desktop', 'effort' => 2]);
        Item::factory()->create(['title' => 'Responder e-mails', 'effort' => 1]);
        // 8º item: o critério de verificação do brief exige count >= 8
        Item::factory()->create(['title' => 'Ligar para o dentista', 'due_date' => now()->addDays(2), 'effort' => 1]);
    }
}

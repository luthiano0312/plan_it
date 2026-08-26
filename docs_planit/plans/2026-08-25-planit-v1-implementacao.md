# PlanIt v1 — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

## Context

O usuário convive com várias frentes de organização sobrepostas (tarefas, escola, projetos, anotações…) e dois sintomas centrais: paralisia ao decidir por onde começar e falta de vontade por atrito alto mesmo sabendo o que fazer. A hipótese do PlanIt: **reduzir atrito + protocolos prontos**. O v1 aprovado (`docs_planit/plans/planit-v1-design.md`, status "aprovado para plano de implementação") constrói só o núcleo: Items hierárquicos (tarefa/projeto/passo), lista curta priorizada ("Agora"), quebra em passos e cronômetro global. Hábitos, rotinas, anotações e revisão espaçada ficam fora do v1. Este plano transforma essa spec em tarefas executáveis, respeitando a ordem que ela mesma sugere (modelo de dados → scorer TDD → regras de status/timer TDD → API → telas → PWA).

**Goal:** Construir o núcleo do PlanIt v1 conforme a spec — backend Laravel 13 API-only com SQLite, frontend React/Vite em TypeScript empacotado como PWA instalável.

**Architecture:** Monorepo com `backend/` (Laravel API-only, sem autenticação, single-user local) e `frontend/` (SPA React 19 + Vite 8, TS). Regras de domínio centralizadas em serviços testáveis (`PriorityScorer`, `ItemTransitionService`, `TimerService`) — nunca espalhadas por controllers. Comunicação REST/JSON via proxy `/api` do dev-server (sem CORS).

**Tech Stack:** PHP 8.5 + Laravel 13 + PHPUnit 12 · SQLite (:memory: nos testes) · Node 22 LTS · Vite 8 + React 19 + TypeScript · react-router v8 · Vitest 4 + Testing Library · vite-plugin-pwa 1.3.

**Spec:** `docs_planit/plans/planit-v1-design.md` — o plano argumenta a partir dela; executores devem ler ambos.

---

## Global Constraints

- **Sem autenticação** — app pessoal single-user, tudo em localhost.
- **Banco**: SQLite arquivo único (`backend/database/database.sqlite`); nos testes, `:memory:` (já configurado no `phpunit.xml` do skeleton — não mexer nele).
- **Backend é API-only**: nenhum Blade/view; tudo REST/JSON em `/api/*`. Erros já voltam como JSON para `api/*` (o skeleton registra `shouldRenderJsonWhen`).
- **Valores de status em ASCII** no banco/código: `'pendente' | 'em_andamento' | 'concluido'` (sem acento). O acento ("concluído") aparece só na UI.
- **Pesos e parâmetros de priorização vivem em `backend/config/planit.php`** — nada hardcoded espalhado.
- **Transições automáticas num único ponto** (`ItemTransitionService`); qualquer código que conclua um Item DEVE passar por ele.
- **Exclusividade global de cronômetro**: no máximo um `TimeEntry` aberto (`ended_at IS NULL`) no sistema inteiro — garantido em transação no serviço + índice único parcial no banco.
- **TDD obrigatório nas regras de domínio** (scorer, transições, timer, tempo total). Feature tests nos endpoints. Testes leves de comportamento no frontend (não pixel-a-pixel).
- **Estilo de teste PHPUnit do skeleton**: métodos com prefixo `test_` (sem atributo `#[Test]`), seguindo os exemplos do próprio skeleton.
- **UI em português (pt-BR)**.
- Commits pequenos e frequentes, conventional commits (`feat:`, `test:`, `chore:`, `docs:`), terminando com `Co-Authored-By: Claude <noreply@anthropic.com>`.
- Executar na branch `feat/planit-v1` criada do `main`.

## Decisões registradas (spec não fixava; definidas com o usuário ou aqui)

1. **Layout monorepo** dentro do repo existente: `backend/` e `frontend/` ao lado de `docs_planit/`.
2. **Deletar projeto → cascade nos descendentes** (`cascadeOnDelete` no `parent_id`) — escolha do usuário. FK constraints ativas no SQLite (default do Laravel 11+, incluindo cascades recursivos self-referential).
3. **`effort` default 3** quando não informado (meio da escala 1–5).
4. **`manual_priority`: menor valor = maior prioridade** (ordenação ascendente; 1 fica no topo da lista).
5. **Iniciar timer no mesmo item com sessão já aberta é idempotente** (retorna a sessão aberta; não empilha).
6. **Reabrir item concluído** (PATCH mudando status para fora de `concluido`) é permitido; zera `completed_at`; não reabre o pai automaticamente.
7. **Fórmulas do scorer (defaults sensatos, configuráveis)**:
   - Urgência: prazo hoje/vencido → `urgency_max` (10, **saturado** — vencido há 1 dia = vencido há 30 dias). Prazo futuro: linear de 10 até 0 ao longo de 14 dias (horizonte). Sem prazo → valor fixo baixo `1.0` (**não** zero).
   - Facilidade: `ease = 6 − effort` (effort 1 → 5; effort 5 → 1).
   - `score = urgência × peso_prazo + facilidade × peso_esforço`; padrões `due_weight = 3`, `ease_weight = 1`.
8. **Fluxo da Criação rápida**: botão principal "Salvar" cria e volta pra tela Agora (captura sem atrito); botão secundário "Marcar como projeto" cria e abre a Tela de Item completa (pra adicionar passos).
9. **Frontend em TypeScript** — escolha explícita do usuário; contratos tipados em `src/api/types.ts` espelhando os Resources do backend.
10. **CORS irrelevante**: proxy `/api` no dev-server e no `preview` do Vite → `http://127.0.0.1:8000`.
11. Grupo de middleware `api` do Laravel 13 **não tem throttle por default** (só `SubstituteBindings`) — nada a fazer para uso local single-user; não chamar `throttleApi()`.

## Ambiente (verificado em 2026-08-25)

| Ferramenta | Versão | Observação |
|---|---|---|
| PHP CLI | 8.5.0 | ini em `C:\php-8.5.0\php.ini`; **pdo_sqlite/sqlite3 presentes mas desabilitados** (Task 0 resolve); `mbstring` já habilitado |
| Composer | 2.8.10 | |
| Node / npm | 22.20.0 LTS / 10.9.3 | atende Vite 8 (^20.19 \|\| >=22.12) |
| Laravel | 13.x (13.29.0 na data) | skeleton usa PHPUnit 12, prefixo `test_` |

⚠️ O `composer create-project` do Laravel 13 roda `artisan migrate --graceful` ao final — **a Task 0 tem que vir antes** do scaffold, senão falha.

## Estrutura de arquivos (destino final)

```
plan_it/
├── docs_planit/plans/planit-v1-design.md        (existente — spec)
├── README.md                                     (Task 15: como rodar)
├── backend/
│   ├── app/
│   │   ├── Enums/ItemStatus.php
│   │   ├── Models/{Item,TimeEntry}.php
│   │   ├── Services/{PriorityScorer,TimerService,ItemTransitionService}.php
│   │   └── Http/
│   │       ├── Controllers/Api/{ItemController,NowController,TimerController}.php
│   │       └── Resources/{ItemResource,NowItemResource}.php
│   ├── config/planit.php
│   ├── database/
│   │   ├── factories/{ItemFactory,TimeEntryFactory}.php
│   │   ├── migrations/…_create_items_table.php · …_create_time_entries_table.php
│   │   └── database.sqlite
│   ├── routes/api.php
│   └── tests/{Unit,Feature}/…
└── frontend/
    ├── public/{logo.svg, favicon.ico, favicon.svg, pwa-64x64.png,
    │           pwa-192x192.png, pwa-512x512.png, maskable-icon-512x512.png,
    │           apple-touch-icon-180x180.png}     (pngs/ico gerados na Task 14)
    └── src/
        ├── api/{client,items,timer,types}.ts
        ├── test/testUtils.tsx                    (helper de stub de fetch)
        ├── components/{Layout,StepForm,TimerPanel}.tsx
        ├── pages/{NowPage,ItemPage,NewItemPage,AllItemsPage}.tsx + *.test.tsx
        └── {App,main}.tsx
```

---

## Contratos centrais (todas as tasks consomem isto)

**Enum** `App\Enums\ItemStatus: string` — casos `Pendente='pendente'`, `EmAndamento='em_andamento'`, `Concluido='concluido'`.

**Model `App\Models\Item`**
- Fillable: `title, description, parent_id, due_date, effort, manual_priority, status, completed_at`
- Casts: `status => ItemStatus::class`, `due_date => date`, `completed_at => datetime`, `effort => integer`, `manual_priority => float`
- Relações: `parent(): BelongsTo` (fk `parent_id`), `children(): HasMany` (fk `parent_id`), `timeEntries(): HasMany`
- Helpers: `isLeaf(): bool`, `descendantIds(): Collection` (BFS por níveis, evita N+1), `scopeActionable(Builder)` (status ≠ concluído E nenhum filho com status ≠ concluído)

**Model `App\Models\TimeEntry`** — fillable `item_id, started_at, ended_at`; casts datetime; `item(): BelongsTo`.

**Serviços (assinaturas exatas)**
```php
// App\Services\PriorityScorer — lê config('planit.priority.*') no construtor
public function urgency(?CarbonInterface $dueDate): float   // saturação no teto quando hoje/vencido
public function ease(int $effort): float                     // 6 - effort
public function score(Item $item): float                     // urg*peso_prazo + ease*peso_esforço
public function shortlist(?int $limit = null): Collection    // actionable; manual_priority asc primeiro,
                                                             // depois score desc; take(limit); anexa ->score
// App\Services\ItemTransitionService — ÚNICO ponto de transição automática
public function markInProgressIfNeeded(Item $item): void     // só se pendente
public function complete(Item $item): Item                   // conclui sempre permitido + completed_at; propaga pros pais
public function propagateCompletion(Item $child): void       // pai com todos os filhos concluídos → concluído, recursivo
// App\Services\TimerService — injeta ItemTransitionService
public function start(Item $item): TimeEntry                 // DB::transaction: fecha aberto alheio, idempotente
                                                             // no mesmo item, cria sessão, marca em_andamento
public function stopCurrent(): ?TimeEntry                    // fecha a sessão aberta (qualquer item)
public function current(): ?TimeEntry                        // sessão aberta com ->item carregado, ou null
public function totalSeconds(Item $item): int                // próprias + TODOS os descendentes; abertas contam até now()
```

**API REST** (prefixo `/api`, registrado via `withRouting(api:)`):
| Método | Rota | Ação |
|---|---|---|
| GET | `/api/items?status=&project=` | lista geral c/ filtros |
| POST | `/api/items` | criação rápida |
| GET | `/api/items/{id}` | detalhe: item + children + time_sessions + total_seconds |
| PATCH | `/api/items/{id}` | edição (mudança p/ `concluido` passa por `complete()`) |
| DELETE | `/api/items/{id}` | apaga (cascade nos filhos) |
| GET | `/api/now` | shortlist priorizada (`planit.shortlist_size`, padrão 5) |
| POST | `/api/items/{id}/timer/start` | inicia cronômetro (fecha o anterior) |
| POST | `/api/timer/stop` | pausa o cronômetro atual |
| GET | `/api/timer/current` | sessão aberta + item, ou `null` |

**Shape JSON** — `ItemResource`: `{ id, title, description, parent_id, parent_title, due_date (YYYY-MM-DD|null), effort, manual_priority, status (string), completed_at (ISO|null), is_leaf, total_seconds }`. Em `show` acrescenta `children[]` (mesma shape, sem netos) e `time_sessions[]`: `{ id, started_at, ended_at, duration_seconds }`. `NowItemResource`: `{ id, title, parent_title, due_date, effort, status, is_running, score }`.

**Tipos TS espelho** (`frontend/src/api/types.ts`):
```ts
export type ItemStatus = 'pendente' | 'em_andamento' | 'concluido'

export interface PlanItem {
  id: number; title: string; description: string | null
  parent_id: number | null; parent_title: string | null
  due_date: string | null; effort: number; manual_priority: number | null
  status: ItemStatus; completed_at: string | null
  is_leaf: boolean; total_seconds: number
}

export interface PlanItemDetail extends PlanItem {
  children: PlanItem[]
  time_sessions: TimeSession[]
}

export interface TimeSession {
  id: number; started_at: string; ended_at: string | null; duration_seconds: number
}

export interface NowItem {
  id: number; title: string; parent_title: string | null; due_date: string | null
  effort: number; status: ItemStatus; is_running: boolean; score: number
}
```

---

### Task 0: Habilitar SQLite no PHP

⚠️ **Única mudança fora do repositório** (usuário aprovou editar o php.ini).

**Files:** Modify: `C:\php-8.5.0\php.ini` (sistema, não versionado)

- [ ] **Step 1: Descomentar as extensões**

No `php.ini`, remover o `;` das linhas (as DLLs já estão em `C:\php-8.5.0\ext\`; não há problema de ordem entre elas):
```ini
extension=pdo_sqlite
extension=sqlite3
```
Se `extension_dir` estiver comentado, descomentar também a forma Windows: `extension_dir = "ext"`.

- [ ] **Step 2: Verificar**

Run: `php -m | grep -iE "sqlite|mbstring"`
Expected: `pdo_sqlite`, `sqlite3` e `mbstring` listados (`mbstring` é obrigatório pro Laravel e já está ativo nesta máquina — conferir mesmo assim antes do create-project).

(Não há commit — nada no repo mudou.)

---

### Task 1: Scaffold do backend Laravel + limpeza do scaffolding desnecessário

**Files:**
- Create: `backend/` (via create-project), `backend/routes/api.php`
- Delete: `backend/app/Models/User.php`, `backend/package.json`, `backend/vite.config.js`, `backend/.npmrc`, `backend/resources/js/**`, `backend/AGENTS.md`, `backend/CLAUDE.md` (o skeleton 13.x vem com clutter Node/Tailwind e guias de agente — API-only não usa)

**Interfaces:**
- Produces: app Laravel funcional em `backend/`, SQLite migrado, rotas `/api` registradas, `php artisan test` verde.

- [ ] **Step 1: Criar o projeto** (do root do repo; exige Task 0 concluída):

```bash
composer create-project laravel/laravel backend
```

- [ ] **Step 2: Conferir `.env`** — o skeleton 13.x já traz `DB_CONNECTION=sqlite` ativo (MySQL comentado) e sem `DB_DATABASE` (default `database/database.sqlite`, criado automaticamente pelo create-project). Só conferir.

- [ ] **Step 3: Remover scaffolding de usuários SEM quebrar sessions/cache/queue**:
  - Em `database/migrations/0001_01_01_000000_create_users_table.php`: **manter só o bloco `Schema::create('sessions')`** (o `.env` default usa `SESSION_DRIVER=database`); deletar os blocos `users` e `password_reset_tokens` do mesmo arquivo.
  - Deletar `app/Models/User.php`.
  - Zerar `DatabaseSeeder::run()` (corpo vazio — senão o seeder crasha chamando `User::factory()`; Task 6 preenche).
  - `config/auth.php` pode ficar (referências são lazy/não usadas).

- [ ] **Step 4: Limpar clutter Node/Tailwind** — deletar `package.json`, `vite.config.js`, `.npmrc`, `resources/js/**` e os guias `AGENTS.md`/`CLAUDE.md` do backend. Não deletar `resources/views` inteiro se algo referenciar (checar `bootstrap/app.php` e `routes/web.php` — manter `welcome.blade.php` e a rota web intactos é aceitável; API-only significa apenas que NENHUMA funcionalidade vive em views).

- [ ] **Step 5: Criar `backend/routes/api.php`:**

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/healthcheck', fn () => response()->json(['ok' => true]));
```

Registrar em `backend/bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

(isso aplica o middleware `api` + prefixo `/api`; sem Sanctum, sem `install:api`)

- [ ] **Step 6: Verificar**

Run: `php artisan migrate:fresh --force` → Expected: OK (só tabela sessions/jobs/cache).
Run: `php artisan serve` num terminal e, em outro: `curl http://127.0.0.1:8000/api/healthcheck` → Expected: `{"ok":true}`
Run: `php artisan test` → Expected: PASS (ExampleTest incluído).

- [ ] **Step 7: Copiar este plano pra dentro do repo** — salvar cópia em `docs_planit/plans/2026-08-25-planit-v1-implementacao.md` (o plano viaja com o código).

- [ ] **Step 8: Commit**

```bash
git add -A
git commit -m "chore: scaffold backend Laravel 13 (API-only, SQLite)"
```

---

### Task 2: Model Item — migration, enum, factory, helpers

**Files:**
- Create: `backend/database/migrations/…_create_items_table.php`, `backend/app/Enums/ItemStatus.php`, `backend/app/Models/Item.php`, `backend/database/factories/ItemFactory.php`
- Test: `backend/tests/Unit/ItemTest.php`

**Interfaces:**
- Produces: `Item` exatamente como em **Contratos centrais** (relações `parent()/children()/timeEntries()` — a última conecta na Task 5).

- [ ] **Step 1: Escrever testes falhando** (`tests/Unit/ItemTest.php`, classe com `use Illuminate\Foundation\Testing\RefreshDatabase;` + `uses(RefreshDatabase::class)`):

```php
use App\Enums\ItemStatus;
use App\Models\Item;

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
```

- [ ] **Step 2: Ver falhar** — Run: `php artisan test tests/Unit/ItemTest.php` → Expected: FAIL (migration/model não existem).

- [ ] **Step 3: Implementar**

Migration:

```php
Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->foreignId('parent_id')->nullable()->constrained('items')->cascadeOnDelete();
    $table->date('due_date')->nullable();
    $table->unsignedTinyInteger('effort')->default(3);
    $table->decimal('manual_priority', 8, 2)->nullable();
    $table->string('status')->default('pendente')->index();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

Enum (casos em **Contratos centrais**). Model com fillable/casts/relações/helpers do contrato; `descendantIds()` em BFS:

```php
public function descendantIds(): Collection
{
    $ids = collect();
    $frontier = collect([$this->id]);
    while ($frontier->isNotEmpty()) {
        $frontier = static::query()->whereIn('parent_id', $frontier)->pluck('id');
        $ids = $ids->merge($frontier);
    }
    return $ids;
}
```

`ItemFactory`: `title` fake sentence, `effort` randomInt(1,5), demais nullable/default; estados `concluded()` (status `concluido` + `completed_at` now) e `forParent(Item $p)` (`parent_id` do pai).

- [ ] **Step 4: Ver passar** — Run: `php artisan test tests/Unit/ItemTest.php` → Expected: PASS (4 testes).

- [ ] **Step 5: Commit** — `feat: model Item com hierarquia, enum de status e escopo actionable`

---

### Task 3: PriorityScorer (TDD)

**Files:**
- Create: `backend/config/planit.php`, `backend/app/Services/PriorityScorer.php`
- Test: `backend/tests/Unit/PriorityScorerTest.php`

**Interfaces:**
- Consumes: `Item::actionable()` (Task 2)
- Produces: `urgency/ease/score/shortlist` (**Contratos centrais**) — Task 8 injeta no `NowController`.

- [ ] **Step 1: Escrever testes falhando** — uma regra da spec por teste (setup base: `config()->set('planit.priority.*', …)` com os defaults da decisão 7; helper local `scorer()` retorna o serviço):

```php
use App\Models\Item;
use App\Services\PriorityScorer;
use Illuminate\Support\Carbon;

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
    $ontem = Item::factory()->create(['due_date' => Carbon::yesterday()]);
    $haMes = Item::factory()->create(['due_date' => Carbon::today()->subDays(30)]);

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

public function test_shortlist_exclui_projeto_com_passos_pendentes_e_limita_tamanho(): void
{
    $projeto = Item::factory()->create();
    Item::factory()->times(7)->create(['parent_id' => $projeto->id]);

    $lista = $this->scorer()->shortlist(3);

    $this->assertCount(3, $lista);
    $this->assertFalse($lista->contains(fn ($i) => $i->id === $projeto->id));
}
```

- [ ] **Step 2: Ver falhar** → Expected: FAIL (serviço/config não existem).

- [ ] **Step 3: Implementar**

`config/planit.php` (todos via `env()`, defaults da decisão 7):

```php
return [
    'priority' => [
        'due_weight' => env('PLANIT_DUE_WEIGHT', 3.0),
        'ease_weight' => env('PLANIT_EASE_WEIGHT', 1.0),
        'urgency_max' => env('PLANIT_URGENCY_MAX', 10.0),
        'urgency_horizon_days' => env('PLANIT_URGENCY_HORIZON_DAYS', 14),
        'urgency_no_due' => env('PLANIT_URGENCY_NO_DUE', 1.0),
    ],
    'shortlist_size' => env('PLANIT_SHORTLIST_SIZE', 5),
];
```

Serviço lê `config()` no construtor (testes sobrescrevem com `config()->set()`); fórmulas da decisão 7; ordenação do `shortlist()`: `manual_priority` ASC primeiro (nulos por último), depois `score` DESC; `take(limit ?? config('planit.shortlist_size'))`, `values()`, anexando atributo `->score` em cada item.

- [ ] **Step 4: Ver passar** → Expected: PASS (6 testes).

- [ ] **Step 5: Commit** — `feat: PriorityScorer com saturação de urgência e faixa de prioridade manual`

---

### Task 4: ItemTransitionService (TDD) — transições automáticas centralizadas

**Files:**
- Create: `backend/app/Services/ItemTransitionService.php`
- Test: `backend/tests/Unit/ItemTransitionServiceTest.php`

**Interfaces:**
- Consumes: `Item` (Task 2)
- Produces: `markInProgressIfNeeded/complete/propagateCompletion` (**Contratos centrais**) — Task 5 injeta este serviço; Task 7 usa `complete()` no PATCH.

- [ ] **Step 1: Escrever testes falhando:**

```php
use App\Enums\ItemStatus;
use App\Models\Item;
use App\Services\ItemTransitionService;

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
```

- [ ] **Step 2: Ver falhar** → Expected: FAIL.

- [ ] **Step 3: Implementar** (código pronto em **Contratos centrais**: `markInProgressIfNeeded` atualiza só se pendente; `complete` grava status+`completed_at` e chama `propagateCompletion`; `propagateCompletion` sobe pela cadeia de pais enquanto todos os filhos diretos estiverem concluídos e o pai ainda não estiver concluído, marcando cada um).

- [ ] **Step 4: Ver passar** → Expected: PASS (5 testes).

- [ ] **Step 5: Commit** — `feat: transições automáticas de status centralizadas no ItemTransitionService`

---

### Task 5: TimeEntry + TimerService (TDD) — exclusividade global e tempo total

**Files:**
- Create: `backend/database/migrations/…_create_time_entries_table.php`, `backend/app/Models/TimeEntry.php`, `backend/database/factories/TimeEntryFactory.php`, `backend/app/Services/TimerService.php`
- Test: `backend/tests/Unit/TimerServiceTest.php`

**Interfaces:**
- Consumes: `ItemTransitionService` (Task 4, injetado no construtor), `Item::descendantIds()` (Task 2)
- Produces: `start/stopCurrent/current/totalSeconds` (**Contratos centrais**) — Tasks 8/9 expõem pela API.

- [ ] **Step 1: Escrever testes falhando:**

```php
use App\Enums\ItemStatus;
use App\Models\{Item, TimeEntry};
use App\Services\TimerService;
use Illuminate\Support\Carbon;

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
```

- [ ] **Step 2: Ver falhar** → Expected: FAIL (tabela/model/serviço não existem).

- [ ] **Step 3: Implementar**

Migration com **índice único parcial** (defesa no nível do banco contra dupla sessão aberta):

```php
Schema::create('time_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
    $table->timestamp('started_at');
    $table->timestamp('ended_at')->nullable();
    $table->timestamps();
});
DB::statement('CREATE UNIQUE INDEX time_entries_single_open ON time_entries ((1)) WHERE ended_at IS NULL');
```

Model `TimeEntry` conforme contrato. `TimeEntryFactory`: `started_at` now sub minutos, `ended_at` alternando null/data. `TimerService` conforme **Contratos centrais** — `start()` dentro de `DB::transaction`: busca aberta; se do mesmo item retorna (idempotente); se de outro, fecha com `ended_at = now()`; cria a nova; chama `$this->transitions->markInProgressIfNeeded($item)`. `totalSeconds()` soma em PHP sobre `descendantIds()+próprio id`:

```php
$duracao = $e->ended_at
    ? $e->ended_at->diffInSeconds($e->started_at)
    : now()->diffInSeconds($e->started_at);
```

- [ ] **Step 4: Ver passar** → Expected: PASS (7 testes).

- [ ] **Step 5: Commit** — `feat: TimeEntry com exclusividade global e tempo total recursivo`

---

### Task 6: Seed de dados demo

**Files:** Modify: `backend/database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Produces: `php artisan db:seed` popula itens hierárquicos variados pra desenvolver/testar as telas com conteúdo real desde o início.

- [ ] **Step 1: Implementar o seeder** usando `Item::factory()` (dados determinísticos, não aleatórios — usar valores fixos):

```php
public function run(): void
{
    $projetoEscola = Item::factory()->create(['title' => 'Trabalho de cálculo', 'description' => 'Entregar até fim do mês', 'due_date' => now()->addDays(3)]);
    Item::factory()->create(['title' => 'Resolver lista 1', 'parent_id' => $projetoEscola->id, 'due_date' => now()->addDay(), 'effort' => 2]);
    Item::factory()->create(['title' => 'Revisar teoria', 'parent_id' => $projetoEscola->id, 'effort' => 5]);
    Item::factory()->concluded()->create(['title' => 'Separar material', 'parent_id' => $projetoEscola->id]);

    Item::factory()->create(['title' => 'Pagar conta de luz', 'due_date' => now()->subDay(), 'effort' => 1, 'manual_priority' => 1]);
    Item::factory()->create(['title' => 'Organizar desktop', 'effort' => 2]);
    Item::factory()->create(['title' => 'Responder e-mails', 'effort' => 1]);
}
```

- [ ] **Step 2: Verificar** — Run: `php artisan migrate:fresh --seed` → Expected: OK; `php artisan tinker --execute="echo App\Models\Item::count();"` imprime ≥ 8.

- [ ] **Step 3: Commit** — `feat: seed com dados demo hierárquicos`

---

### Task 7: API REST de Items (CRUD) — controllers, resources, feature tests

**Files:**
- Create: `backend/app/Http/Controllers/Api/ItemController.php`, `backend/app/Http/Resources/ItemResource.php`
- Modify: `backend/routes/api.php`
- Test: `backend/tests/Feature/ItemsApiTest.php`

**Interfaces:**
- Consumes: `ItemResource` shape (**Contratos centrais**), `ItemTransitionService::complete()` (Task 4), `TimerService::totalSeconds()` (Task 5)
- Produces: endpoints de items da tabela REST — Task 9 consome no frontend.

- [ ] **Step 1: Escrever testes de feature falhando** (`tests/Feature/ItemsApiTest.php`) — cobrir comportamento + efeito no banco, não CRUD trivial:

```php
use App\Enums\ItemStatus;
use App\Models\{Item, TimeEntry};

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
```

- [ ] **Step 2: Ver falhar** → Run: `php artisan test tests/Feature/ItemsApiTest.php` → Expected: FAIL (rotas 404).

- [ ] **Step 3: Implementar** — `ItemController` com validação em store/update (`title required|string|max:255`, `description nullable|string`, `parent_id nullable|exists:items,id` + nunca permitir ancestral próprio, `due_date nullable|date`, `effort integer|between:1,5`, `manual_priority nullable|numeric|min:0`, `status in:pendente,em_andamento,concluido` só no update). **Regra centralizada**: no `update()`, se `status` vira `concluido` → `$this->transitions->complete($item)` (nunca update direto); se sai de `concluido` → update normal + `completed_at = null`. Index com filtros `status` (valor do enum) e `project` (id de pai) usando `when(...)`, eager load `parent`, com `withCount('children')` pra derivar `is_leaf`. Show carrega `children` + `timeEntries` recentes e calcula `total_seconds` via `TimerService`. Resources montam a shape dos **Contratos centrais** (`parent_title` via relação carregada; `duration_seconds` por sessão; datas formatadas).

Rotas:

```php
Route::apiResource('items', ItemController::class);
```

- [ ] **Step 4: Ver passar** → Expected: PASS (7 testes).

- [ ] **Step 5: Commit** — `feat: API REST de items com propagação de conclusão`

---

### Task 8: API /now + endpoints de timer — feature tests

**Files:**
- Create: `backend/app/Http/Controllers/Api/NowController.php`, `backend/app/Http/Controllers/Api/TimerController.php`, `backend/app/Http/Resources/NowItemResource.php`
- Modify: `backend/routes/api.php`
- Test: `backend/tests/Feature/NowAndTimerApiTest.php`

**Interfaces:**
- Consumes: `PriorityScorer::shortlist()` (Task 3), `TimerService` (Task 5)
- Produces: `/api/now`, `/api/items/{id}/timer/start`, `/api/timer/stop`, `/api/timer/current` — telas do frontend dependem deles.

- [ ] **Step 1: Escrever testes falhando:**

```php
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
```

- [ ] **Step 2: Ver falhar** → Expected: FAIL (404).

- [ ] **Step 3: Implementar** — `NowController` invokable injetando `PriorityScorer`, retornando `NowItemResource::collection($scorer->shortlist())` com `is_running` (sessão aberta no item?) e `score`. `TimerController` com `start($itemId)` (findOrFail + `TimerService::start`, 201 + entry/item), `stop()` (`stopCurrent`, 200 com entry fechada ou `data: null`), `current()` (`current()` com `TimeEntry` resource ou `data: null`). Rotas:

```php
Route::get('/now', NowController::class);
Route::post('/items/{item}/timer/start', [TimerController::class, 'start']);
Route::post('/timer/stop', [TimerController::class, 'stop']);
Route::get('/timer/current', [TimerController::class, 'current']);
```

- [ ] **Step 4: Ver passar** → Expected: PASS.

- [ ] **Step 5: Commit** — `feat: endpoint /now e controle de cronômetro via API`

---

### Task 9: Scaffold do frontend (Vite + React 19 + TS) — cliente da API, rotas, setup de testes

**Files:**
- Create: `frontend/` (template `react-ts`), `frontend/src/api/{client,items,timer,types}.ts`, `frontend/src/components/Layout.tsx`, `frontend/src/App.tsx` (router), `frontend/src/test/testUtils.tsx`
- Modify: `frontend/vite.config.ts` (proxy + depois PWA), `frontend/package.json` (deps/scripts)
- Delete: boilerplate do template (`App.css`, assets default, conteúdo do `index.css` substituído)

**Interfaces:**
- Produces: módulos `fetchNow()`, `listItems(params?)`, `getItem(id)`, `createItem(data)`, `updateItem(id,data)`, `deleteItem(id)`, `startTimer(itemId)`, `stopTimer()`, `fetchCurrentTimer()` (tipados com os tipos de **Contratos centrais**); rotas `/` , `/novo`, `/items`, `/items/:id`; helper `stubFetch()` pros testes das Tasks 10–13.

- [ ] **Step 1: Criar o projeto** (do root):

```bash
npm create vite@latest frontend -- --template react-ts
cd frontend && npm install
npm install react-router
npm install -D vitest jsdom @testing-library/react @testing-library/user-event @testing-library/dom
```

(`react-router` v8 — o pacote `react-router-dom` foi removido na v8; imports vêm de `'react-router'`)

- [ ] **Step 2: Configurar** — scripts no `package.json`: `"test": "vitest run"`, `"watch": "vitest"`. Proxy no `vite.config.ts`:

```ts
server: { proxy: { '/api': 'http://127.0.0.1:8000' } },
preview: { proxy: { '/api': 'http://127.0.0.1:8000' } },
```

`vitest.config.ts` separado (evita interferência de plugins futuros, ex. PWA):

```ts
import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  test: { environment: 'jsdom', globals: true },
})
```

- [ ] **Step 3: Tipos + cliente HTTP** — `src/api/types.ts` exatamente como em **Contratos centrais**; `src/api/client.ts`:

```ts
const BASE = '/api'

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const res = await fetch(`${BASE}${path}`, {
    headers: { 'Content-Type': 'application/json', ...(options.headers ?? {}) },
    ...options,
  })
  if (!res.ok) throw new Error(`API ${res.status}: ${await res.text()}`)
  return res.status === 204 ? (undefined as T) : ((await res.json()) as T)
}

export const api = {
  get: <T>(p: string) => request<T>(p),
  post: <T>(p: string, body?: unknown) =>
    request<T>(p, { method: 'POST', body: JSON.stringify(body ?? {}) }),
  patch: <T>(p: string, body: unknown) =>
    request<T>(p, { method: 'PATCH', body: JSON.stringify(body) }),
  del: <T>(p: string) => request<T>(p, { method: 'DELETE' }),
}
```

`src/api/items.ts` e `src/api/timer.ts` com as funções do Interfaces acima (payloads tipados). **Convenção de envelope**: *collections* do Laravel vêm embrulhadas (`{ data: [...] }`) — desembrulhar nos módulos da API (ex.: `api.get<{ data: NowItem[] }>('/now').then(r => r.data)`, idem `listItems`); *recurso único* (`getItem`, respostas de timer) vem solto no topo — retornar direto.

- [ ] **Step 4: Router + Layout** — `src/App.tsx` com `BrowserRouter/Routes/Route` importados de `'react-router'`; `Layout.tsx` com nav fixa (links: **Agora** `/`, **Novo** `/novo`, **Todos** `/items`) e `<Outlet/>`; `main.tsx` renderiza `<App/>` com `BrowserRouter` e CSS mínimo limpo (tema claro simples; sem Tailwind).

- [ ] **Step 5: Helper de teste** — `src/test/testUtils.tsx`:

```tsx
import { render } from '@testing-library/react'
import { MemoryRouter } from 'react-router'
import { vi } from 'vitest'
import type { ReactElement } from 'react'

export function renderWithRouter(ui: ReactElement, { route = '/' } = {}) {
  return render(<MemoryRouter initialEntries={[route]}>{ui}</MemoryRouter>)
}

export function stubFetch(
  routes: { method?: string; match: RegExp; body?: unknown }[],
) {
  const fn = vi.fn(async (input: RequestInfo | URL, init?: RequestInit) => {
    const url = String(input)
    const method = init?.method ?? 'GET'
    const hit = routes.find((r) => (r.method ?? 'GET') === method && r.match.test(url))
    return new Response(JSON.stringify(hit?.body ?? {}), {
      status: hit ? 200 : 404,
      headers: { 'Content-Type': 'application/json' },
    })
  })
  vi.stubGlobal('fetch', fn)
  return fn
}
```

- [ ] **Step 6: Smoke test** — `src/App.test.tsx`: renderiza `App` com fetch stubado retornando `[]` pra `/api/now` e verifica que o texto "Agora" aparece.

- [ ] **Step 7: Verificar** — Run: `npm test` → Expected: PASS; Run: `php artisan serve` (backend/) + `npm run dev` (frontend/) → abrir `http://localhost:5173` mostra a nav; aba Network confirma proxy `/api/now → 8000` sem erro de CORS.

- [ ] **Step 8: Commit** — `feat: scaffold frontend React+Vite+TS com cliente da API e rotas`

---

### Task 10: Tela 1 "Agora" — shortlist + botão começar

**Files:**
- Create: `frontend/src/pages/NowPage.tsx`, `frontend/src/pages/NowPage.test.tsx`
- Modify: `frontend/src/App.tsx` (rota index → NowPage)

**Interfaces:**
- Consumes: `fetchNow()`, `startTimer()` (Task 9); `GET /api/now` (Task 8)
- Produces: componente inicial da rota `/`.

- [ ] **Step 1: Escrever teste falhando:**

```tsx
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { NowPage } from './NowPage'
import { renderWithRouter, stubFetch } from '../test/testUtils'

const agora = [
  { id: 7, title: 'Estudar cálculo', parent_title: 'Escola', due_date: '2026-08-30', effort: 2, status: 'pendente', is_running: false, score: 18.5 },
  { id: 9, title: 'Pagar luz', parent_title: null, due_date: '2026-08-20', effort: 1, status: 'pendente', is_running: true, score: 30 },
]

beforeEach(() => stubFetch([
  { match: /\/api\/now$/, body: { data: agora } },
]))

describe('NowPage', () => {
  it('mostra a lista curta com projeto pai e prazo', async () => {
    renderWithRouter(<NowPage />)
    expect(await screen.findByText('Estudar cálculo')).toBeDefined()
    expect(screen.getByText(/Escola/)).toBeDefined()
    expect(screen.getByText(/2026-08-30/)).toBeDefined()
  })

  it('botão Começar inicia o cronômetro e marca rodando', async () => {
    const fetchMock = stubFetch([
      { match: /\/api\/now$/, body: { data: agora } },
      { method: 'POST', match: /\/api\/items\/7\/timer\/start$/, body: {} },
    ])
    renderWithRouter(<NowPage />)

    await userEvent.click(await screen.findByRole('button', { name: /começar/i }))

    expect(fetchMock).toHaveBeenCalledWith(
      expect.stringContaining('/api/items/7/timer/start'),
      expect.objectContaining({ method: 'POST' }),
    )
    expect(await screen.findByText(/⏱ rodando/i)).toBeDefined()
  })
})
```

- [ ] **Step 2: Ver falhar** → Run: `npm test` → Expected: FAIL (componente não existe).

- [ ] **Step 3: Implementar** — `NowPage.tsx`: `useEffect` carrega `fetchNow()`; lista com título (link → `/items/:id`), `parent_title`, prazo (classe `overdue` se `due_date < hoje`), badge "⏱ rodando" quando `is_running`, botão **Começar** que chama `startTimer(item.id)` e passa a mostrar o badge; estado vazio: "Nada na fila. Crie um item." com link pra `/novo`.

- [ ] **Step 4: Ver passar** → Run: `npm test` → Expected: PASS.

- [ ] **Step 5: Verificação visual rápida** — com backend servindo + seed (Tasks 1/6), abrir `/` e conferir a lista com dados reais.

- [ ] **Step 6: Commit** — `feat: tela Agora com lista curta e início de cronômetro`

---

### Task 11: Tela 2 "Item" — detalhe, passos rápidos e painel de cronômetro

**Files:**
- Create: `frontend/src/pages/ItemPage.tsx`, `frontend/src/pages/ItemPage.test.tsx`, `frontend/src/components/StepForm.tsx`, `frontend/src/components/TimerPanel.tsx`
- Modify: `frontend/src/App.tsx` (rota `/items/:id`)

**Interfaces:**
- Consumes: `getItem/updateItem/createItem/deleteItem` (Task 9), `startTimer/stopTimer/fetchCurrentTimer`, `GET /api/items/:id` (Task 7/8)
- Produces: destino dos links da NowPage e do redirect da NewItemPage (Task 12).

- [ ] **Step 1: Escrever testes falhando** — casos-chave:

```tsx
const detalhe = {
  id: 7, title: 'Trabalho de cálculo', description: 'Cap 1–3', parent_id: null, parent_title: null,
  due_date: '2026-09-01', effort: 3, manual_priority: null, status: 'em_andamento',
  completed_at: null, is_leaf: false, total_seconds: 3660,
  children: [{ id: 12, title: 'Resolver lista 1', description: null, parent_id: 7, parent_title: null,
    due_date: null, effort: 2, manual_priority: null, status: 'pendente', completed_at: null,
    is_leaf: true, total_seconds: 0 }],
  time_sessions: [{ id: 55, started_at: '2026-08-24T10:00:00Z', ended_at: '2026-08-24T11:01:00Z', duration_seconds: 3660 }],
}

// testes:
// 1) renderiza título, descrição, prazo, esforço, tempo total ("01:01:00") e breadcrumb
// 2) criar passo: digitar no StepForm e Enter → POST /api/items com parent_id 7 e title digitado
// 3) marcar passo concluído (checkbox) → PATCH /api/items/12 { status: 'concluido' }
// 4) botão "Iniciar" → POST /api/items/7/timer/start; botão "Pausar" → POST /api/timer/stop
// 5) concluir item → PATCH /api/items/7 { status: 'concluido' }
```

(usar `stubFetch` com as rotas `/\/api\/items\/7$/` etc.; assertar corpo via `fetchMock.mock.calls`)

- [ ] **Step 2: Ver falhar** → Expected: FAIL.

- [ ] **Step 3: Implementar** —
  - `ItemPage.tsx`: carrega `getItem(useParams().id)`; campos editáveis (título, descrição textarea, prazo date-input, esforço select 1–5, prioridade manual number) com botão Salvar → `updateItem`; checkbox de conclusão → `updateItem(id, { status })`; breadcrumb do pai (`/items/:parent_id`); seção de filhos com `StepForm` e checkboxes; `TimerPanel`; botão excluir (confirm) → volta pra `/`.
  - `StepForm.tsx`: input "Adicionar passo…" + Enter → `createItem({ title, parent_id })` e recarrega detalhe.
  - `TimerPanel.tsx`: props `{ itemId, isRunning, startedAt?, totalSeconds, sessions }`; botão Iniciar/Pausar; contador vivo (setInterval 1 s somando `totalSeconds` desde o load quando rodando); histórico de sessões (título "Sessões", linhas com duração `hh:mm:ss`).

- [ ] **Step 4: Ver passar** → Expected: PASS.

- [ ] **Step 5: Verificação visual** — fluxo real: iniciar cronômetro num passo, ver contador subir, pausar, concluir ambos os passos e ver o projeto concluir sozinho (valida Task 4 ponta a ponta).

- [ ] **Step 6: Commit** — `feat: tela de item com passos rápidos e painel de cronômetro`

---

### Task 12: Tela 3 "Criação rápida"

**Files:**
- Create: `frontend/src/pages/NewItemPage.tsx`, `frontend/src/pages/NewItemPage.test.tsx`
- Modify: `frontend/src/App.tsx` (rota `/novo`)

**Interfaces:**
- Consumes: `createItem()` (Task 9), `POST /api/items` (Task 7)
- Produces: formulário de captura rápida; redirect conforme decisão 8.

- [ ] **Step 1: Escrever testes falhando:**

```tsx
// 1) submit com só título → POST /api/items com { title } e navega pra '/' (captura sem atrito)
// 2) "Marcar como projeto" → mesmo POST e navega pra /items/<id retornado>
// 3) sem título → botões desabilitados (ou erro inline), sem chamada de API
```

(navegação assertada com `MemoryRouter` + um `<Routes>` de captura que renderiza a rota alvo; payload conferido via `fetchMock.mock.calls[0]`)

- [ ] **Step 2: Ver falhar** → Expected: FAIL.

- [ ] **Step 3: Implementar** — `NewItemPage.tsx`: campos título (autofocus), descrição, prazo (date), esforço (select 1–5, default 3), prioridade manual (opcional); **Salvar** → `createItem(payload)` → `navigate('/')`; **Marcar como projeto** → `createItem(payload)` → `navigate(`/items/${criado.id}`)`.

- [ ] **Step 4: Ver passar** → Expected: PASS.

- [ ] **Step 5: Commit** — `feat: tela de criação rápida com modo projeto`

---

### Task 13: Tela 4 "Lista geral"

**Files:**
- Create: `frontend/src/pages/AllItemsPage.tsx`, `frontend/src/pages/AllItemsPage.test.tsx`
- Modify: `frontend/src/App.tsx` (rota `/items`)

**Interfaces:**
- Consumes: `listItems(params)` (Task 9), `GET /api/items?status=&project=` (Task 7)
- Produces: navegação fora da shortlist.

- [ ] **Step 1: Escrever testes falhando:**

```tsx
// 1) renderiza itens com badges de status/prazo vencido/cronômetro rodando
// 2) mudar filtro de status refaz o GET com query param (?status=concluido)
// 3) clicar num item navega pra /items/:id (Link)
```

- [ ] **Step 2: Ver falhar** → Expected: FAIL.

- [ ] **Step 3: Implementar** — `AllItemsPage.tsx`: filtro status (select: todos/pendente/em_andamento/concluído), filtro projeto (select com projetos = itens raiz que têm filhos; opção "todos"); tabela/lista com título (link), projeto pai, prazo, esforço, status; badges: vencido (prazo < hoje e não concluído), ⏱ se `is_running` (via `fetchCurrentTimer` comparado aos ids).

- [ ] **Step 4: Ver passar** → Expected: PASS.

- [ ] **Step 5: Commit** — `feat: lista geral com filtros de status e projeto`

---

### Task 14: Empacotamento PWA

**Files:**
- Create: `frontend/public/logo.svg` (fonte dos ícones)
- Generated: `favicon.ico`, `favicon.svg`, `pwa-64x64.png`, `pwa-192x192.png`, `pwa-512x512.png`, `maskable-icon-512x512.png`, `apple-touch-icon-180x180.png` (em `public/`)
- Modify: `frontend/index.html` (meta theme-color + title/lang), `frontend/vite.config.ts` (plugin PWA), `frontend/package.json` (devDep)

**Interfaces:**
- Consumes: app SPA pronto (Tasks 9–13)
- Produces: build instalável em `dist/`, servível por qualquer servidor estático em localhost.

- [ ] **Step 1: Criar `public/logo.svg`** — quadrado arredondado `#4f46e5` com "P" branco bold centralizado (SVG simples, ~10 linhas).

- [ ] **Step 2: Gerar os ícones:**

```bash
npx pwa-assets-generator --preset minimal-2023 public/logo.svg
```

Expected: os 7 arquivos listados acima aparecem em `public/`.

- [ ] **Step 3: Instalar e configurar o plugin:**

```bash
npm install -D vite-plugin-pwa
```

`vite.config.ts`:

```ts
plugins: [
  react(),
  VitePWA({
    registerType: 'autoUpdate',
    includeAssets: ['favicon.ico', 'favicon.svg', 'apple-touch-icon-180x180.png'],
    manifest: {
      name: 'PlanIt',
      short_name: 'PlanIt',
      description: 'Itens, prioridades e cronômetro',
      theme_color: '#4f46e5',
      background_color: '#ffffff',
      display: 'standalone',
      start_url: '/',
      icons: [
        { src: 'pwa-64x64.png', sizes: '64x64', type: 'image/png' },
        { src: 'pwa-192x192.png', sizes: '192x192', type: 'image/png' },
        { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png' },
        { src: 'maskable-icon-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
      ],
    },
  }),
],
```

(`registerType: 'autoUpdate'` injeta o registro do service worker automaticamente — **nenhum** código `virtual:pwa-register` no `main.tsx`.) Em `index.html`: `<html lang="pt-BR">`, `<meta name="theme-color" content="#4f46e5" />`, `<link rel="icon" href="/favicon.ico" />`, `<title>PlanIt</title>`.

- [ ] **Step 4: Verificar build e instalabilidade**

Run: `npm run build && npm run preview`
Run: `curl -s http://localhost:4173/manifest.webmanifest | head -c 300` → Expected: JSON do manifesto
Run: `curl -s http://localhost:4173/sw.js | head -c 120` → Expected: service worker servido
Manual: abrir `http://localhost:4173` no Chrome → ícone de instalação na omnibox → instalar → abre janela própria sem barra de navegador (secure-context vale pra `localhost`).

- [ ] **Step 5: Commit** — `feat: empacotamento PWA com manifest e ícones gerados`

---

### Task 15: README + smoke test ponta a ponta

**Files:**
- Create: `README.md` (root do repo)

- [ ] **Step 1: Escrever o README** — pré-requisitos (PHP 8.5 com `pdo_sqlite` habilitado, Composer, Node 22 LTS), setup (`composer install` no backend + `cp .env` já pronto; `npm install` no frontend), como rodar dev (2 terminais: `php artisan serve` + `npm run dev` → `http://localhost:5173`), como rodar testes (`php artisan test` / `npm test`), seed (`php artisan migrate:fresh --seed`), instalação PWA (build+preview ou instalar direto do dev-server), nota da evolução futura pra Electron (troca só da casca, spec).

- [ ] **Step 2: Smoke test completo** — com seed carregado, percorrer manualmente: criar item rápido → aparece na lista geral; marcar como projeto → adicionar 2 passos; iniciar cronômetro num passo → badge na Agora; iniciar no outro item → anterior pausa sozinho; concluir passos → projeto conclui sozinho; filtros da lista geral funcionam.

- [ ] **Step 3: Suítes verdes** — Run: `cd backend && php artisan test` → PASS; Run: `cd frontend && npm test` → PASS.

- [ ] **Step 4: Commit** — `docs: instruções de execução e verificação final do v1`

---

## Self-review (feito na escrita deste plano)

- **Cobertura da spec**: modelo de dados (Tasks 2/5) ✓ · exclusividade do timer (Task 5 + índice parcial) ✓ · transições automáticas centralizadas (Task 4, consumidas por 5/7) ✓ · PriorityScorer com as 3 regras da spec (Task 3) ✓ · telas 1–4 (Tasks 10–13) ✓ · REST/JSON localhost (Tasks 7–9) ✓ · PWA instalável (Task 14) ✓ · estratégia de testes da spec (TDD domínio, feature API, leves no FE) ✓ · ordem sugerida da spec respeitada ✓.
- **Dependências entre tasks acíclicas e na ordem**: Item → Scorer → Transitions → Timer → API → FE (Timer injeta Transition; API injeta Scorer+Timer; FE consome contratos fixados).
- **Sem placeholders**: todo step tem código, comando e resultado esperado; corpos marcados com padrão explícito a seguir têm o código-fonte correspondente definido em Contratos centrais ou no próprio bloco.
- **Consistência de tipos/nomes**: assinaturas PHP e interfaces TS declaradas uma única vez em **Contratos centrais** e referenciadas textualmente pelas tasks.

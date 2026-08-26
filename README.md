# PlanIt

Organizador pessoal local: itens (tarefas/projetos), lista curta priorizada, quebra em passos e cronômetro. v1 = só o núcleo; sem autenticação, tudo roda em localhost.

- Spec: [`docs_planit/plans/planit-v1-design.md`](docs_planit/plans/planit-v1-design.md)
- Plano de implementação: [`docs_planit/plans/2026-08-25-planit-v1-implementacao.md`](docs_planit/plans/2026-08-25-planit-v1-implementacao.md)

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 13 (API only), PHP 8.5 |
| Banco | SQLite (`backend/database/database.sqlite`) |
| Frontend | React 19 + Vite + TypeScript, empacotado como PWA |

## Pré-requisitos

- **PHP 8.5** com as extensões `pdo_sqlite` e `sqlite3` habilitadas no `php.ini`
  (em instalações Windows elas costumam vir desabilitadas — descomente
  `extension=pdo_sqlite` e `extension=sqlite3`). Conferir com `php -m | findstr sqlite`.
- **Composer** 2.x
- **Node.js 22 LTS**

## Setup

Backend:

```bash
cd backend
composer install
cp .env.example .env        # já vem configurado com DB_CONNECTION=sqlite
php artisan key:generate
touch database/database.sqlite   # Windows PowerShell: ni database/database.sqlite
php artisan migrate --seed
```

Frontend:

```bash
cd frontend
npm install
```

## Rodando em desenvolvimento (2 terminais)

```bash
# terminal 1 — API em http://127.0.0.1:8000
cd backend && php artisan serve

# terminal 2 — app em http://localhost:5173
cd frontend && npm run dev
```

O Vite faz proxy de `/api` para o backend, então não há CORS a configurar.

## Testes

```bash
cd backend  && php artisan test   # domínio (TDD) + feature dos endpoints
cd frontend && npm test           # testes leves de comportamento das telas
```

## Dados de exemplo (seed)

```bash
cd backend && php artisan migrate:fresh --seed
```

Cria uma semana de exemplos: projetos com passos, prazos variados (incluindo vencidos) e um item avulso.

## Instalação como app (PWA)

O frontend é um PWA instalável (manifest + service worker com auto-update):

```bash
cd frontend && npm run build && npm run preview
# abre http://localhost:4173
```

Em `http://localhost:4173` (ou no dev-server `5173`) o Chrome mostra o ícone de instalação na omnibox — instalar abre o PlanIt em janela própria, sem barra de navegador. `localhost` conta como secure-context, então não precisa de HTTPS.

## Endpoints principais

Base: `http://127.0.0.1:8000/api`

- `GET /now` — lista curta priorizada + item com cronômetro rodando
- `GET/POST /items`, `GET/PATCH/DELETE /items/{id}` — CRUD (hierarquia via `parent_id`; PATCH pode mudar `status`)
- `POST /items/{id}/timer/start` · `POST /timer/stop` · `GET /timer/current`
- Healthcheck: `GET /api/healthcheck`

## Evolução futura

Se um dia fizerem falta recursos nativos (ícone na taskbar, notificações do SO), o caminho planejado é migrar essa mesma base (frontend web + API localhost) para **Electron** — troca só da "casca", sem reescrever lógica.

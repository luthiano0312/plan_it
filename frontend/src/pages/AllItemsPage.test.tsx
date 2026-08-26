import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Route, Routes } from 'react-router'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { AllItemsPage } from './AllItemsPage'
import { renderWithRouter, stubFetch, type StubRoute } from '../test/testUtils'
import type { PlanItem } from '../api/types'

const base = {
  description: null,
  parent_id: null,
  parent_title: null,
  manual_priority: null,
  completed_at: null,
  is_leaf: true,
  total_seconds: 0,
}

const itens: PlanItem[] = [
  {
    ...base,
    id: 3,
    title: 'Conta de luz',
    due_date: '2026-08-01',
    effort: 1,
    status: 'pendente',
  },
  {
    ...base,
    id: 5,
    title: 'Arquivo antigo',
    due_date: null,
    effort: 2,
    status: 'concluido',
  },
  {
    ...base,
    id: 8,
    title: 'Estudar inglês',
    due_date: null,
    effort: 3,
    status: 'em_andamento',
  },
]

function stubListagem(rotas: StubRoute[] = []) {
  return stubFetch([
    { match: /\/api\/items(\?|$)/, body: { data: itens } },
    { match: /\/api\/timer\/current$/, body: { data: { id: 9, item_id: 8 } } },
    ...rotas,
  ])
}

function renderLista() {
  return renderWithRouter(
    <Routes>
      <Route path="/items" element={<AllItemsPage />} />
      <Route path="/items/:id" element={<p>destino-item</p>} />
    </Routes>,
    { route: '/items' },
  )
}

beforeEach(() => vi.unstubAllGlobals())

describe('AllItemsPage', () => {
  it('renderiza itens com badges de vencido e cronômetro rodando', async () => {
    stubListagem()

    renderLista()

    expect(await screen.findByText('Conta de luz')).toBeDefined()
    expect(screen.getByText('vencido')).toBeDefined()
    expect(screen.getByText(/⏱/)).toBeDefined()
    expect(screen.getByText('concluído')).toBeDefined()
  })

  it('mudar filtro de status refaz o GET com query param', async () => {
    const fetchMock = stubListagem()

    renderLista()

    await screen.findByText('Conta de luz')
    await userEvent.selectOptions(screen.getByLabelText(/status/i), 'concluido')

    expect(
      fetchMock.mock.calls.some(([url]) => String(url).includes('/api/items?status=concluido')),
    ).toBe(true)
  })

  it('clicar num item navega pra tela dele', async () => {
    stubListagem()

    renderLista()

    await userEvent.click(await screen.findByText('Conta de luz'))
    expect(await screen.findByText('destino-item')).toBeDefined()
  })
})

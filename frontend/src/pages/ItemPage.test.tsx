import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Routes, Route } from 'react-router'
import { describe, expect, it } from 'vitest'
import { ItemPage } from './ItemPage'
import { renderWithRouter, stubFetch, type StubRoute } from '../test/testUtils'

const detalhe = {
  id: 7,
  title: 'Trabalho de cálculo',
  description: 'Cap 1–3',
  parent_id: null,
  parent_title: null,
  due_date: '2026-09-01',
  effort: 3,
  manual_priority: null,
  status: 'em_andamento',
  completed_at: null,
  is_leaf: false,
  total_seconds: 3660,
  children: [
    {
      id: 12,
      title: 'Resolver lista 1',
      description: null,
      parent_id: 7,
      parent_title: null,
      due_date: null,
      effort: 2,
      manual_priority: null,
      status: 'pendente',
      completed_at: null,
      is_leaf: true,
      total_seconds: 0,
    },
  ],
  time_sessions: [
    {
      id: 55,
      started_at: '2026-08-24T10:00:00Z',
      ended_at: '2026-08-24T11:01:00Z',
      duration_seconds: 3660,
    },
  ],
}

function stubItem(rotas: StubRoute[] = []) {
  return stubFetch([
    { match: /\/api\/items\/7$/, body: detalhe },
    { match: /\/api\/timer\/current$/, body: { data: null } },
    ...rotas,
  ])
}

function renderPage(route = '/items/7') {
  return renderWithRouter(
    <Routes>
      <Route path="/items/:id" element={<ItemPage />} />
    </Routes>,
    { route },
  )
}

describe('ItemPage', () => {
  it('renderiza título, descrição, prazo, esforço, tempo total e sessões', async () => {
    stubItem()

    renderPage()

    expect(await screen.findByDisplayValue('Trabalho de cálculo')).toBeDefined()
    expect(screen.getByDisplayValue('Cap 1–3')).toBeDefined()
    expect(screen.getByDisplayValue('2026-09-01')).toBeDefined()
    expect((screen.getByLabelText(/esforço/i) as HTMLSelectElement).value).toBe('3')
    // contador grande e a linha da sessão podem coincidir no formato hh:mm:ss
    expect((await screen.findAllByText('01:01:00')).length).toBeGreaterThan(0)
    expect(screen.getByText('Sessões')).toBeDefined()
  })

  it('criar passo via StepForm faz POST com parent_id e título digitado', async () => {
    const fetchMock = stubItem([
      { method: 'POST', match: /\/api\/items$/, body: { id: 99 } },
    ])

    renderPage()

    const input = await screen.findByPlaceholderText('Adicionar passo…')
    await userEvent.type(input, 'Comprar material{enter}')

    const post = fetchMock.mock.calls.find(
      ([url, init]) =>
        String(url).endsWith('/api/items') &&
        (init as RequestInit | undefined)?.method === 'POST',
    )
    expect(post).toBeDefined()
    expect(JSON.parse(String(post![1]?.body))).toEqual({
      title: 'Comprar material',
      parent_id: 7,
    })
  })

  it('marcar passo concluído faz PATCH com status concluido', async () => {
    const fetchMock = stubItem([
      { method: 'PATCH', match: /\/api\/items\/12$/, body: {} },
    ])

    renderPage()

    const checkbox = await screen.findByRole('checkbox', { name: 'Resolver lista 1' })
    await userEvent.click(checkbox)

    const patch = fetchMock.mock.calls.find(
      ([url, init]) =>
        String(url).endsWith('/api/items/12') &&
        (init as RequestInit | undefined)?.method === 'PATCH',
    )
    expect(patch).toBeDefined()
    expect(JSON.parse(String(patch![1]?.body))).toEqual({ status: 'concluido' })
  })

  it('botões Iniciar e Pausar controlam o cronômetro', async () => {
    const fetchMock = stubItem([
      { method: 'POST', match: /\/api\/items\/7\/timer\/start$/, body: {} },
      { method: 'POST', match: /\/api\/timer\/stop$/, body: { data: null } },
    ])

    renderPage()

    await userEvent.click(await screen.findByRole('button', { name: /iniciar/i }))

    const start = fetchMock.mock.calls.find(
      ([url, init]) =>
        String(url).endsWith('/api/items/7/timer/start') &&
        (init as RequestInit | undefined)?.method === 'POST',
    )
    expect(start).toBeDefined()

    await userEvent.click(await screen.findByRole('button', { name: /pausar/i }))

    const stop = fetchMock.mock.calls.find(
      ([url, init]) =>
        String(url).endsWith('/api/timer/stop') &&
        (init as RequestInit | undefined)?.method === 'POST',
    )
    expect(stop).toBeDefined()
  })

  it('concluir o próprio item faz PATCH no item', async () => {
    const fetchMock = stubItem([
      { method: 'PATCH', match: /\/api\/items\/7$/, body: {} },
    ])

    renderPage()

    const checkbox = await screen.findByRole('checkbox', {
      name: /concluir trabalho de cálculo/i,
    })
    await userEvent.click(checkbox)

    const patch = fetchMock.mock.calls.find(
      ([url, init]) =>
        String(url).endsWith('/api/items/7') &&
        (init as RequestInit | undefined)?.method === 'PATCH',
    )
    expect(patch).toBeDefined()
    expect(JSON.parse(String(patch![1]?.body))).toEqual({ status: 'concluido' })
  })
})

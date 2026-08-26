import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { beforeEach, describe, expect, it } from 'vitest'
import { NowPage } from './NowPage'
import { renderWithRouter, stubFetch } from '../test/testUtils'

const agora = [
  {
    id: 7,
    title: 'Estudar cálculo',
    parent_title: 'Escola',
    due_date: '2026-08-30',
    effort: 2,
    status: 'pendente',
    is_running: false,
    score: 18.5,
  },
  {
    id: 9,
    title: 'Pagar luz',
    parent_title: null,
    due_date: '2026-08-20',
    effort: 1,
    status: 'pendente',
    is_running: true,
    score: 30,
  },
]

beforeEach(() =>
  stubFetch([{ match: /\/api\/now$/, body: { data: agora } }]),
)

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

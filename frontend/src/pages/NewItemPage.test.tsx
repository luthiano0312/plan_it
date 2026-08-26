import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Route, Routes } from 'react-router'
import { describe, expect, it } from 'vitest'
import { NewItemPage } from './NewItemPage'
import { renderWithRouter, stubFetch } from '../test/testUtils'

function renderNovo() {
  return renderWithRouter(
    <Routes>
      <Route path="/" element={<p>destino-raiz</p>} />
      <Route path="/novo" element={<NewItemPage />} />
      <Route path="/items/:id" element={<p>destino-item</p>} />
    </Routes>,
    { route: '/novo' },
  )
}

function chamadaPostItems(fetchMock: ReturnType<typeof stubFetch>) {
  return fetchMock.mock.calls.find(
    ([url, init]) =>
      String(url).endsWith('/api/items') &&
      (init as RequestInit | undefined)?.method === 'POST',
  )
}

describe('NewItemPage', () => {
  it('submit com só título faz POST e navega pra raiz', async () => {
    const fetchMock = stubFetch([
      { method: 'POST', match: /\/api\/items$/, body: { id: 1 } },
    ])

    renderNovo()

    await userEvent.type(await screen.findByLabelText(/título/i), 'Comprar pão')
    await userEvent.click(screen.getByRole('button', { name: /^salvar$/i }))

    expect(await screen.findByText('destino-raiz')).toBeDefined()
    const post = chamadaPostItems(fetchMock)
    expect(post).toBeDefined()
    expect(JSON.parse(String(post![1]?.body))).toEqual({ title: 'Comprar pão' })
  })

  it('marcar como projeto navega pro item criado', async () => {
    const fetchMock = stubFetch([
      { method: 'POST', match: /\/api\/items$/, body: { id: 42 } },
    ])

    renderNovo()

    await userEvent.type(await screen.findByLabelText(/título/i), 'Reforma do quarto')
    await userEvent.click(screen.getByRole('button', { name: /marcar como projeto/i }))

    expect(await screen.findByText('destino-item')).toBeDefined()
    const post = chamadaPostItems(fetchMock)
    expect(JSON.parse(String(post![1]?.body))).toEqual({ title: 'Reforma do quarto' })
  })

  it('sem título os botões ficam desabilitados e nada é enviado', async () => {
    const fetchMock = stubFetch([
      { method: 'POST', match: /\/api\/items$/, body: {} },
    ])

    renderNovo()

    const salvar = (await screen.findByRole('button', { name: /^salvar$/i })) as HTMLButtonElement
    const projeto = (screen.getByRole('button', {
      name: /marcar como projeto/i,
    }) as HTMLButtonElement).disabled

    expect(salvar.disabled).toBe(true)
    expect(projeto).toBe(true)

    await userEvent.click(salvar)
    expect(chamadaPostItems(fetchMock)).toBeUndefined()
  })
})

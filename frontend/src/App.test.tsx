import { screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import App from './App'
import { renderWithRouter, stubFetch } from './test/testUtils'

describe('App', () => {
  it('mostra a navegação com a tela Agora', () => {
    stubFetch([{ match: /\/api\/now$/, body: { data: [] } }])
    renderWithRouter(<App />)
    expect(screen.getByText('Agora')).toBeDefined()
    expect(screen.getByText('Novo')).toBeDefined()
    expect(screen.getByText('Todos')).toBeDefined()
  })
})

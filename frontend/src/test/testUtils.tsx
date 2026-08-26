import { render } from '@testing-library/react'
import { MemoryRouter } from 'react-router'
import { vi } from 'vitest'
import type { ReactElement } from 'react'

export function renderWithRouter(ui: ReactElement, { route = '/' }: { route?: string } = {}) {
  return render(<MemoryRouter initialEntries={[route]}>{ui}</MemoryRouter>)
}

export interface StubRoute {
  method?: string
  match: RegExp
  body?: unknown
}

export function stubFetch(routes: StubRoute[]) {
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

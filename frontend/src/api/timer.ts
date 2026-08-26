import { api } from './client'
import type { PlanItem, TimeSession } from './types'

export type TimerSession = TimeSession & { item_id: number; item?: PlanItem }

/** Inicia o cronômetro no item (fecha qualquer sessão aberta alheia). */
export function startTimer(itemId: number): Promise<TimerSession> {
  return api.post<TimerSession>(`/items/${itemId}/timer/start`)
}

export async function stopTimer(): Promise<TimerSession | null> {
  const res = await api.post<{ data: TimerSession | null }>('/timer/stop')
  return res.data
}

export async function fetchCurrentTimer(): Promise<TimerSession | null> {
  return api.get<{ data: TimerSession | null }>('/timer/current').then((r) => r.data)
}

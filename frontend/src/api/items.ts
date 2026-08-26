import { api } from './client'
import type { NowItem, PlanItem, PlanItemDetail } from './types'

export interface ItemPayload {
  title?: string
  description?: string | null
  parent_id?: number | null
  due_date?: string | null
  effort?: number
  manual_priority?: number | null
  status?: ItemPayloadStatus
}

type ItemPayloadStatus = 'pendente' | 'em_andamento' | 'concluido'

/** Lista curta priorizada da tela Agora. */
export async function fetchNow(): Promise<NowItem[]> {
  return api.get<{ data: NowItem[] }>('/now').then((r) => r.data)
}

export interface ListItemsParams {
  status?: string
  project?: number | null
}

export async function listItems(params?: ListItemsParams): Promise<PlanItem[]> {
  const query = new URLSearchParams()
  if (params?.status) query.set('status', params.status)
  if (params?.project != null) query.set('project', String(params.project))
  const qs = query.toString()
  return api.get<{ data: PlanItem[] }>(`/items${qs ? `?${qs}` : ''}`).then((r) => r.data)
}

export function getItem(id: number): Promise<PlanItemDetail> {
  return api.get<PlanItemDetail>(`/items/${id}`)
}

export function createItem(data: ItemPayload): Promise<PlanItem> {
  return api.post<PlanItem>('/items', data)
}

export function updateItem(id: number, data: ItemPayload): Promise<PlanItem> {
  return api.patch<PlanItem>(`/items/${id}`, data)
}

export function deleteItem(id: number): Promise<void> {
  return api.del<void>(`/items/${id}`)
}

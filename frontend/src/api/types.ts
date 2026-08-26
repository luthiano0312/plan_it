export type ItemStatus = 'pendente' | 'em_andamento' | 'concluido'

export interface PlanItem {
  id: number
  title: string
  description: string | null
  parent_id: number | null
  parent_title: string | null
  due_date: string | null
  effort: number
  manual_priority: number | null
  status: ItemStatus
  completed_at: string | null
  is_leaf: boolean
  total_seconds: number
}

export interface PlanItemDetail extends PlanItem {
  children: PlanItem[]
  time_sessions: TimeSession[]
}

export interface TimeSession {
  id: number
  started_at: string
  ended_at: string | null
  duration_seconds: number
}

export interface NowItem {
  id: number
  title: string
  parent_title: string | null
  due_date: string | null
  effort: number
  status: ItemStatus
  is_running: boolean
  score: number
}

import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router'
import { fetchCurrentTimer } from '../api/timer'
import { listItems } from '../api/items'
import type { ItemStatus, PlanItem } from '../api/types'

function hojeISO(): string {
  return new Date().toISOString().slice(0, 10)
}

const STATUS: Array<{ valor: '' | ItemStatus; rotulo: string }> = [
  { valor: '', rotulo: 'todos' },
  { valor: 'pendente', rotulo: 'pendente' },
  { valor: 'em_andamento', rotulo: 'em andamento' },
  { valor: 'concluido', rotulo: 'concluído' },
]

export function AllItemsPage() {
  const [itens, setItens] = useState<PlanItem[] | null>(null)
  const [status, setStatus] = useState<'' | ItemStatus>('')
  const [projeto, setProjeto] = useState('')
  const [rodandoId, setRodandoId] = useState<number | null>(null)

  const carregar = useCallback(async () => {
    const lista = await listItems({
      status: status || undefined,
      project: projeto !== '' ? Number(projeto) : undefined,
    })
    setItens(lista)
    const atual = await fetchCurrentTimer()
    setRodandoId(atual?.item_id ?? null)
  }, [status, projeto])

  useEffect(() => {
    carregar().catch(() => setItens([]))
  }, [carregar])

  if (itens === null) return <p>Carregando…</p>

  const projetos = itens.filter((i) => i.parent_id === null && !i.is_leaf)
  const hoje = hojeISO()

  return (
    <div>
      <div style={{ display: 'flex', gap: 12 }}>
        <label>
          Status{' '}
          <select
            aria-label="Filtrar por status"
            value={status}
            onChange={(e) => setStatus(e.target.value as '' | ItemStatus)}
          >
            {STATUS.map((s) => (
              <option key={s.valor} value={s.valor}>
                {s.rotulo}
              </option>
            ))}
          </select>
        </label>
        <label>
          Projeto{' '}
          <select
            aria-label="Filtrar por projeto"
            value={projeto}
            onChange={(e) => setProjeto(e.target.value)}
          >
            <option value="">todos</option>
            {projetos.map((p) => (
              <option key={p.id} value={p.id}>
                {p.title}
              </option>
            ))}
          </select>
        </label>
      </div>

      <ul style={{ listStyle: 'none', padding: 0 }}>
        {itens.map((item) => {
          const vencido =
            item.due_date !== null && item.due_date < hoje && item.status !== 'concluido'
          return (
            <li
              key={item.id}
              style={{
                background: '#ffffff',
                border: '1px solid #e4e4ea',
                borderRadius: 10,
                padding: '10px 14px',
                marginBottom: 8,
              }}
            >
              <Link to={`/items/${item.id}`} style={{ fontWeight: 600, color: '#1c1c22' }}>
                {item.title}
              </Link>
              <span style={{ marginLeft: 8, color: '#5a5a66' }}>{item.status}</span>
              {vencido && <span className="overdue" style={{ marginLeft: 6 }}>vencido</span>}
              {rodandoId === item.id && <span style={{ marginLeft: 6 }}>⏱</span>}
              <div style={{ color: '#5a5a66', fontSize: '0.85rem' }}>
                {item.parent_title && <span>{item.parent_title} · </span>}
                {item.due_date && <span>prazo {item.due_date} · </span>}
                <span>esforço {item.effort}</span>
              </div>
            </li>
          )
        })}
      </ul>

      {itens.length === 0 && <p>Nenhum item encontrado.</p>}
    </div>
  )
}

export default AllItemsPage

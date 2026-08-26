import { useEffect, useState } from 'react'
import { Link } from 'react-router'
import { fetchNow } from '../api/items'
import { startTimer } from '../api/timer'
import type { NowItem } from '../api/types'

function hojeISO(): string {
  return new Date().toISOString().slice(0, 10)
}

export function NowPage() {
  const [itens, setItens] = useState<NowItem[] | null>(null)
  const [rodandoId, setRodandoId] = useState<number | null>(null)

  useEffect(() => {
    let ativo = true
    fetchNow()
      .then((lista) => {
        if (!ativo) return
        setItens(lista)
        setRodandoId(lista.find((i) => i.is_running)?.id ?? null)
      })
      .catch(() => {
        if (ativo) setItens([])
      })
    return () => {
      ativo = false
    }
  }, [])

  async function comecar(item: NowItem): Promise<void> {
    await startTimer(item.id)
    setRodandoId(item.id)
  }

  if (itens === null) return <p>Carregando…</p>

  if (itens.length === 0)
    return (
      <p>
        Nada na fila. <Link to="/novo">Crie um item.</Link>
      </p>
    )

  return (
    <ul className="lista-agora" style={{ listStyle: 'none', padding: 0 }}>
      {itens.map((item) => (
        <li
          key={item.id}
          style={{
            background: '#ffffff',
            border: '1px solid #e4e4ea',
            borderRadius: 10,
            padding: '12px 14px',
            marginBottom: 10,
          }}
        >
          <Link to={`/items/${item.id}`} style={{ fontWeight: 600, color: '#1c1c22' }}>
            {item.title}
          </Link>
          <div style={{ display: 'flex', gap: 8, alignItems: 'center', marginTop: 4, color: '#5a5a66', fontSize: '0.85rem' }}>
            {item.parent_title && <span>{item.parent_title}</span>}
            {item.due_date && (
              <span className={item.due_date < hojeISO() ? 'overdue' : undefined}>
                {item.due_date}
              </span>
            )}
            {rodandoId === item.id && <span aria-live="polite">⏱ rodando</span>}
            {rodandoId !== item.id && (
              <button onClick={() => comecar(item)} style={{ marginLeft: 'auto' }}>
                Começar
              </button>
            )}
          </div>
        </li>
      ))}
    </ul>
  )
}

export default NowPage

import { useCallback, useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router'
import { deleteItem, getItem, updateItem } from '../api/items'
import { fetchCurrentTimer } from '../api/timer'
import type { PlanItemDetail } from '../api/types'
import { StepForm } from '../components/StepForm'
import { TimerPanel } from '../components/TimerPanel'

export function ItemPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [item, setItem] = useState<PlanItemDetail | null>(null)
  const [rodandoAqui, setRodandoAqui] = useState(false)
  const [inicioSessao, setInicioSessao] = useState<string | null>(null)

  const recarregar = useCallback(async () => {
    if (!id) return
    const detalhe = await getItem(Number(id))
    setItem(detalhe)
    const atual = await fetchCurrentTimer()
    setRodandoAqui(atual?.item_id === detalhe.id)
    setInicioSessao(atual?.started_at ?? null)
  }, [id])

  useEffect(() => {
    recarregar().catch(() => {})
  }, [recarregar])

  if (!item) return <p>Carregando…</p>

  async function salvar(): Promise<void> {
    await updateItem(item!.id, {
      title: item!.title,
      description: item!.description,
      due_date: item!.due_date,
      effort: item!.effort,
      manual_priority: item!.manual_priority,
    })
    await recarregar()
  }

  async function alternarConclusao(alvo: PlanItemDetail['children'][number] | PlanItemDetail): Promise<void> {
    const novoStatus = alvo.status === 'concluido' ? 'pendente' : 'concluido'
    await updateItem(alvo.id, { status: novoStatus })
    await recarregar()
  }

  async function excluir(): Promise<void> {
    if (!window.confirm('Excluir este item (e os passos dele)?')) return
    await deleteItem(item!.id)
    navigate('/')
  }

  return (
    <article>
      {item.parent_id !== null && (
        <nav aria-label="breadcrumb">
          <Link to={`/items/${item.parent_id}`}>← {item.parent_title ?? 'item pai'}</Link>
        </nav>
      )}

      <label htmlFor="titulo">Título</label>
      <input
        id="titulo"
        value={item.title}
        onChange={(e) => setItem({ ...item, title: e.target.value })}
      />
      <label>
        <input
          type="checkbox"
          checked={item.status === 'concluido'}
          onChange={() => alternarConclusao(item)}
          aria-label={`Concluir ${item.title}`}
        />{' '}
        concluído
      </label>

      <label htmlFor="descricao">Descrição</label>
      <textarea
        id="descricao"
        value={item.description ?? ''}
        onChange={(e) => setItem({ ...item, description: e.target.value })}
      />

      <div>
        <div>
          <label htmlFor="prazo">Prazo</label>
          <input
            id="prazo"
            type="date"
            value={item.due_date ?? ''}
            onChange={(e) => setItem({ ...item, due_date: e.target.value || null })}
          />
        </div>
        <div>
          <label htmlFor="esforco">Esforço</label>
          <select
            id="esforco"
            value={String(item.effort)}
            onChange={(e) => setItem({ ...item, effort: Number(e.target.value) })}
          >
            {[1, 2, 3, 4, 5].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label htmlFor="prioridade">Prioridade manual</label>
          <input
            id="prioridade"
            type="number"
            min={0}
            value={item.manual_priority ?? ''}
            onChange={(e) =>
              setItem({ ...item, manual_priority: e.target.value === '' ? null : Number(e.target.value) })
            }
          />
        </div>
      </div>

      <button onClick={salvar}>Salvar</button>
      <button onClick={excluir}>Excluir</button>

      <TimerPanel
        itemId={item.id}
        isRunning={rodandoAqui}
        startedAt={inicioSessao}
        totalSeconds={item.total_seconds}
        sessions={item.time_sessions}
      />

      <section aria-label="Passos">
        <h2>Passos</h2>
        <ul style={{ listStyle: 'none', padding: 0 }}>
          {item.children.map((passo) => (
            <li key={passo.id}>
              <label>
                <input
                  type="checkbox"
                  checked={passo.status === 'concluido'}
                  onChange={() => alternarConclusao(passo)}
                  aria-label={passo.title}
                />{' '}
                <Link to={`/items/${passo.id}`}>{passo.title}</Link>
              </label>
            </li>
          ))}
        </ul>
        <StepForm parentId={item.id} onCriado={() => void recarregar()} />
      </section>
    </article>
  )
}

export default ItemPage

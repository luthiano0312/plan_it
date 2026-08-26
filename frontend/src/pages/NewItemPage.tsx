import { useState } from 'react'
import type { FormEvent } from 'react'
import { useNavigate } from 'react-router'
import { createItem, type ItemPayload } from '../api/items'

export function NewItemPage() {
  const navigate = useNavigate()
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [dueDate, setDueDate] = useState('')
  const [effort, setEffort] = useState('')
  const [manualPriority, setManualPriority] = useState('')
  const [salvando, setSalvando] = useState(false)

  const podeSalvar = title.trim() !== '' && !salvando

  function payload(): ItemPayload {
    // campos vazios ficam de fora: defaults do backend cobrem (esforço 3)
    const dados: ItemPayload = { title: title.trim() }
    if (description.trim() !== '') dados.description = description
    if (dueDate !== '') dados.due_date = dueDate
    if (effort !== '') dados.effort = Number(effort)
    if (manualPriority !== '') dados.manual_priority = Number(manualPriority)
    return dados
  }

  async function salvar(destino: 'raiz' | 'projeto'): Promise<void> {
    if (!podeSalvar) return
    setSalvando(true)
    try {
      const criado = await createItem(payload())
      navigate(destino === 'projeto' ? `/items/${criado.id}` : '/')
    } finally {
      setSalvando(false)
    }
  }

  function submeter(event: FormEvent): void {
    event.preventDefault()
    void salvar('raiz')
  }

  return (
    <form onSubmit={submeter}>
      <label htmlFor="novo-titulo">Título</label>
      <input
        id="novo-titulo"
        autoFocus
        value={title}
        onChange={(e) => setTitle(e.target.value)}
      />

      <label htmlFor="nova-descricao">Descrição</label>
      <textarea
        id="nova-descricao"
        value={description}
        onChange={(e) => setDescription(e.target.value)}
      />

      <div>
        <div>
          <label htmlFor="novo-prazo">Prazo</label>
          <input
            id="novo-prazo"
            type="date"
            value={dueDate}
            onChange={(e) => setDueDate(e.target.value)}
          />
        </div>
        <div>
          <label htmlFor="novo-esforco">Esforço</label>
          <select
            id="novo-esforco"
            value={effort}
            onChange={(e) => setEffort(e.target.value)}
          >
            <option value="">padrão (3)</option>
            {[1, 2, 3, 4, 5].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label htmlFor="nova-prioridade">Prioridade manual</label>
          <input
            id="nova-prioridade"
            type="number"
            min={0}
            value={manualPriority}
            onChange={(e) => setManualPriority(e.target.value)}
          />
        </div>
      </div>

      <button type="submit" disabled={!podeSalvar}>
        Salvar
      </button>
      <button type="button" disabled={!podeSalvar} onClick={() => void salvar('projeto')}>
        Marcar como projeto
      </button>
    </form>
  )
}

export default NewItemPage

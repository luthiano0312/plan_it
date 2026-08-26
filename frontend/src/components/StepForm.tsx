import { useState } from 'react'
import type { FormEvent } from 'react'
import { createItem } from '../api/items'

interface StepFormProps {
  parentId: number
  onCriado: () => void
}

export function StepForm({ parentId, onCriado }: StepFormProps) {
  const [titulo, setTitulo] = useState('')
  const [salvando, setSalvando] = useState(false)

  async function submeter(event: FormEvent): Promise<void> {
    event.preventDefault()
    const limpo = titulo.trim()
    if (!limpo || salvando) return
    setSalvando(true)
    try {
      await createItem({ title: limpo, parent_id: parentId })
      setTitulo('')
      onCriado()
    } finally {
      setSalvando(false)
    }
  }

  return (
    <form onSubmit={submeter}>
      <input
        value={titulo}
        onChange={(e) => setTitulo(e.target.value)}
        placeholder="Adicionar passo…"
        aria-label="Adicionar passo"
      />
    </form>
  )
}

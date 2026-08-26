import { useEffect, useRef, useState } from 'react'
import { startTimer, stopTimer } from '../api/timer'
import type { TimeSession } from '../api/types'

export function formatarDuracao(segundos: number): string {
  const total = Math.max(0, Math.floor(segundos))
  const h = Math.floor(total / 3600)
  const m = Math.floor((total % 3600) / 60)
  const s = total % 60
  return [h, m, s].map((n) => String(n).padStart(2, '0')).join(':')
}

interface TimerPanelProps {
  itemId: number
  isRunning: boolean
  startedAt?: string | null
  totalSeconds: number
  sessions: TimeSession[]
}

export function TimerPanel({ itemId, isRunning, totalSeconds, sessions }: TimerPanelProps) {
  const [rodando, setRodando] = useState(isRunning)
  const [base, setBase] = useState(totalSeconds)
  const ancoraRef = useRef<number | null>(null)
  const [, forcarTick] = useState(0)

  useEffect(() => {
    if (!rodando) return
    const intervalo = setInterval(() => forcarTick((n) => n + 1), 1000)
    return () => clearInterval(intervalo)
  }, [rodando])

  useEffect(() => {
    if (isRunning && ancoraRef.current === null) {
      // rodando ao carregar a página: conta a partir de agora sobre o total já recebido
      ancoraRef.current = Date.now()
      setRodando(true)
    }
  }, [isRunning])

  const decorridos =
    rodando && ancoraRef.current !== null ? Math.floor((Date.now() - ancoraRef.current) / 1000) : 0

  async function iniciar(): Promise<void> {
    await startTimer(itemId)
    ancoraRef.current = Date.now()
    setRodando(true)
  }

  async function pausar(): Promise<void> {
    await stopTimer()
    setBase(baseAtual())
    ancoraRef.current = null
    setRodando(false)
  }

  function baseAtual(): number {
    return base + decorridos
  }

  return (
    <section aria-label="Cronômetro">
      <p style={{ fontSize: '1.6rem', fontWeight: 700 }}>
        {formatarDuracao(baseAtual())} {rodando && <span>⏱</span>}
      </p>
      {rodando ? (
        <button onClick={pausar}>Pausar</button>
      ) : (
        <button onClick={iniciar}>Iniciar</button>
      )}

      <h3>Sessões</h3>
      {sessions.length === 0 ? (
        <p>Nenhuma sessão ainda.</p>
      ) : (
        <ul>
          {sessions.map((sessao) => (
            <li key={sessao.id}>{formatarDuracao(sessao.duration_seconds)}</li>
          ))}
        </ul>
      )}
    </section>
  )
}

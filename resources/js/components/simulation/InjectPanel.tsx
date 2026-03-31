import { inject } from '@/actions/App/Http/Controllers/SimulationController'
import { useState } from 'react'

type Props = {
    sessionId: string
    disabled: boolean
}

const INJECTABLE_EVENTS = [
    { type: 'simultaneous_exclusion', label: 'Simultaneous Exclusion' },
    { type: 'shootout_start', label: 'Penalty Shootout (jump to end)' },
    { type: 'foul_out', label: 'Foul Out' },
    { type: 'correction', label: 'Score Correction' },
    { type: 'goalkeeper_substitution', label: 'Goalkeeper Substitution' },
]

export default function InjectPanel({ sessionId, disabled }: Props) {
    const [status, setStatus] = useState<string | null>(null)
    const [injecting, setInjecting] = useState(false)

    async function handleInject(eventType: string) {
        setInjecting(true)
        setStatus('Injecting...')

        try {
            const response = await fetch(inject.url(sessionId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ type: eventType }),
            })

            const data = await response.json()
            setStatus(data.success ? 'Injected' : `Failed: ${data.error}`)
        } catch {
            setStatus('Network error')
        } finally {
            setInjecting(false)
            setTimeout(() => setStatus(null), 3000)
        }
    }

    return (
        <div>
            <h3 className="text-sm font-medium text-zinc-700 dark:text-zinc-300">Inject Event</h3>
            <div className="mt-2 flex flex-wrap gap-2">
                {INJECTABLE_EVENTS.map((evt) => (
                    <button
                        key={evt.type}
                        disabled={disabled || injecting}
                        onClick={() => handleInject(evt.type)}
                        className="rounded border border-zinc-300 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50 disabled:opacity-40 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {evt.label}
                    </button>
                ))}
            </div>
            {status && (
                <p className="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{status}</p>
            )}
        </div>
    )
}

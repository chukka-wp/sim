import { router } from '@inertiajs/react'
import { speed } from '@/actions/App/Http/Controllers/SimulationController'

type Props = {
    sessionId: string
    currentSpeed: number
}

const SPEEDS = [0.5, 1, 2, 5, 10]

export default function SpeedControl({ sessionId, currentSpeed }: Props) {
    function setSpeed(value: number) {
        router.post(speed.url(sessionId), { speed: value }, { preserveScroll: true })
    }

    return (
        <div className="flex items-center gap-2">
            <span className="text-xs font-medium text-zinc-500 dark:text-zinc-400">Speed:</span>
            {SPEEDS.map((s) => (
                <button
                    key={s}
                    onClick={() => setSpeed(s)}
                    className={`rounded px-2 py-1 text-xs font-medium transition-colors ${
                        currentSpeed === s
                            ? 'bg-blue-600 text-white'
                            : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700'
                    }`}
                >
                    {s}x
                </button>
            ))}
        </div>
    )
}

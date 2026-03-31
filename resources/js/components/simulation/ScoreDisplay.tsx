import type { SimulationEvent, SimulationStatus } from '@/types/simulation'

type Props = {
    events: SimulationEvent[]
    currentIndex: number
    status: SimulationStatus
}

function deriveScore(events: SimulationEvent[], upToIndex: number) {
    let home = 0
    let away = 0
    let period = 1
    let clock = 0

    for (let i = 0; i <= Math.min(upToIndex, events.length - 1); i++) {
        const event = events[i]
        period = event.period
        clock = event.period_clock_seconds

        if (event.type === 'goal' && event.payload) {
            home = (event.payload.home_score_after as number) ?? home
            away = (event.payload.away_score_after as number) ?? away
        }
    }

    return { home, away, period, clock }
}

function formatClock(seconds: number): string {
    const m = Math.floor(seconds / 60)
    const s = seconds % 60
    return `${m}:${s.toString().padStart(2, '0')}`
}

export default function ScoreDisplay({ events, currentIndex, status }: Props) {
    const { home, away, period, clock } = deriveScore(events, currentIndex)
    const isLive = status === 'playing'

    return (
        <div className="text-center">
            <div className="flex items-center justify-center gap-2">
                {isLive && (
                    <span className="relative flex h-2.5 w-2.5">
                        <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75" />
                        <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500" />
                    </span>
                )}
                <span className="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    {isLive ? 'Live' : status}
                </span>
            </div>

            <div className="mt-3 flex items-baseline justify-center gap-4">
                <div className="text-right">
                    <div className="text-sm font-medium text-blue-600 dark:text-blue-400">Central</div>
                </div>
                <div className="text-4xl font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
                    {home} &ndash; {away}
                </div>
                <div className="text-left">
                    <div className="text-sm font-medium text-zinc-600 dark:text-zinc-400">Easts</div>
                </div>
            </div>

            <div className="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Q{period} &middot; {formatClock(clock)}
            </div>
        </div>
    )
}

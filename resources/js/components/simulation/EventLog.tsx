import type { SimulationEvent, SkippedEvent } from '@/types/simulation'
import { useEffect, useRef } from 'react'

type Props = {
    events: SimulationEvent[]
    currentIndex: number
    skippedEvents: SkippedEvent[]
}

const EVENT_LABELS: Record<string, string> = {
    match_start: 'Match Start',
    match_end: 'Match End',
    period_start: 'Period Start',
    period_end: 'Period End',
    halftime_start: 'Halftime',
    halftime_end: 'Halftime End',
    swim_off: 'Swim Off',
    goal: 'Goal',
    ordinary_foul: 'Ordinary Foul',
    exclusion_foul: 'Exclusion Foul',
    exclusion_expiry: 'Exclusion Expiry',
    penalty_foul: 'Penalty Foul',
    penalty_throw_taken: 'Penalty Throw',
    personal_foul_recorded: 'Personal Foul',
    foul_out: 'Foul Out',
    timeout_start: 'Timeout',
    timeout_end: 'Timeout End',
    substitution: 'Substitution',
    goalkeeper_substitution: 'GK Substitution',
    possession_change: 'Possession Change',
    possession_clock_reset: 'Clock Reset',
    shootout_start: 'Shootout Start',
    shootout_shot: 'Shootout Shot',
    shootout_end: 'Shootout End',
}

function formatTime(isoString: string): string {
    const d = new Date(isoString)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

function eventDescription(event: SimulationEvent): string {
    const p = event.payload
    if (!p || Object.keys(p).length === 0) return ''

    if (event.type === 'goal') {
        const cap = p.cap_number ? `#${p.cap_number}` : ''
        return `${cap} (${p.home_score_after}-${p.away_score_after})`
    }

    if (event.type === 'exclusion_foul' || event.type === 'ordinary_foul') {
        return p.offending_cap_number ? `#${p.offending_cap_number}` : ''
    }

    return ''
}

export default function EventLog({ events, currentIndex, skippedEvents }: Props) {
    const scrollRef = useRef<HTMLDivElement>(null)
    const skippedIndices = new Set(skippedEvents.map((s) => s.index))

    useEffect(() => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight
        }
    }, [currentIndex])

    return (
        <div>
            <div className="flex items-center justify-between">
                <h3 className="text-sm font-medium text-zinc-700 dark:text-zinc-300">Event Log</h3>
                {skippedEvents.length > 0 && (
                    <span className="text-xs text-amber-600 dark:text-amber-400">
                        {skippedEvents.length} skipped
                    </span>
                )}
            </div>

            <div
                ref={scrollRef}
                className="mt-2 h-80 overflow-y-auto rounded-md border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50"
            >
                <div className="divide-y divide-zinc-100 dark:divide-zinc-800">
                    {events.map((event, i) => {
                        const isSent = i < currentIndex
                        const isCurrent = i === currentIndex
                        const isSkipped = skippedIndices.has(i)

                        return (
                            <div
                                key={i}
                                className={`flex items-center gap-3 px-3 py-1.5 text-xs ${
                                    isCurrent
                                        ? 'bg-blue-50 dark:bg-blue-950/30'
                                        : ''
                                }`}
                            >
                                <span className="w-4 text-center">
                                    {isSkipped ? (
                                        <span className="text-amber-500">!</span>
                                    ) : isSent ? (
                                        <span className="text-green-500">&#10003;</span>
                                    ) : isCurrent ? (
                                        <span className="text-blue-500">&rarr;</span>
                                    ) : (
                                        <span className="text-zinc-300 dark:text-zinc-600">&middot;</span>
                                    )}
                                </span>

                                <span className="w-16 tabular-nums text-zinc-400 dark:text-zinc-500">
                                    {formatTime(event.recorded_at)}
                                </span>

                                <span className="w-32 truncate font-medium text-zinc-700 dark:text-zinc-300">
                                    {EVENT_LABELS[event.type] ?? event.type}
                                </span>

                                <span className="text-zinc-500 dark:text-zinc-400">
                                    Q{event.period}
                                </span>

                                <span className="flex-1 truncate text-zinc-400 dark:text-zinc-500">
                                    {eventDescription(event)}
                                </span>
                            </div>
                        )
                    })}
                </div>
            </div>
        </div>
    )
}

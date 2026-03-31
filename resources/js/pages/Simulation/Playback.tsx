import { Link, router } from '@inertiajs/react'
import {
    play,
    pause,
    stop,
    state,
    setup,
} from '@/actions/App/Http/Controllers/SimulationController'
import type { SimulationSessionData } from '@/types/simulation'
import ScoreDisplay from '@/components/simulation/ScoreDisplay'
import EventLog from '@/components/simulation/EventLog'
import SpeedControl from '@/components/simulation/SpeedControl'
import InjectPanel from '@/components/simulation/InjectPanel'
import { useEffect, useRef, useState } from 'react'

type Props = {
    session: SimulationSessionData
    cloudUrl: string
}

export default function Playback({ session: initialSession, cloudUrl }: Props) {
    const [session, setSession] = useState(initialSession)
    const sessionId = initialSession.id

    useEffect(() => {
        const isActive = session.status === 'pending' || session.status === 'playing' || session.status === 'generating'
        if (!isActive) return

        const controller = new AbortController()

        const poll = async () => {
            try {
                const response = await fetch(state.url(sessionId), {
                    signal: controller.signal,
                })
                const data = await response.json()
                setSession((prev) => ({ ...prev, ...data, events: prev.events }))
            } catch (e) {
                if (e instanceof DOMException && e.name === 'AbortError') return
            }
        }

        const interval = setInterval(poll, 1000)

        return () => {
            clearInterval(interval)
            controller.abort()
        }
    }, [session.status, sessionId])

    const canPlay = session.status === 'generated' || session.status === 'paused'
    const canPause = session.status === 'playing'
    const canStop = session.status === 'playing' || session.status === 'paused'
    const isTerminal = ['stopped', 'completed', 'failed'].includes(session.status)

    const progress =
        session.total_events > 0
            ? Math.round((session.current_event_index / session.total_events) * 100)
            : 0

    return (
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <div className="mx-auto max-w-3xl px-6 py-8">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                        chukka-sim
                    </h1>
                    <Link
                        href={setup.url()}
                        className="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        New simulation
                    </Link>
                </div>

                {(session.status === 'pending' || session.status === 'generating') && (
                    <div className="mt-8 text-center">
                        <div className="inline-block h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent" />
                        <p className="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                            Generating events...
                        </p>
                    </div>
                )}

                {session.status === 'failed' && (
                    <div className="mt-8 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/30">
                        <p className="text-sm font-medium text-red-800 dark:text-red-200">
                            Simulation failed
                        </p>
                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                            {session.error_message}
                        </p>
                    </div>
                )}

                {session.status !== 'pending' && session.status !== 'generating' && session.status !== 'failed' && (
                    <>
                        <div className="mt-6 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <ScoreDisplay
                                events={session.events}
                                currentIndex={session.current_event_index}
                                status={session.status}
                            />
                        </div>

                        <div className="mt-4 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div className="flex gap-2">
                                    {canPlay && (
                                        <button
                                            onClick={() =>
                                                router.post(play.url(session.id), {}, { preserveScroll: true })
                                            }
                                            className="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                                        >
                                            &#9654; Play
                                        </button>
                                    )}
                                    {canPause && (
                                        <button
                                            onClick={() =>
                                                router.post(pause.url(session.id), {}, { preserveScroll: true })
                                            }
                                            className="rounded-md bg-amber-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-700"
                                        >
                                            &#9208; Pause
                                        </button>
                                    )}
                                    {canStop && (
                                        <button
                                            onClick={() =>
                                                router.post(stop.url(session.id), {}, { preserveScroll: true })
                                            }
                                            className="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                                        >
                                            &#9209; Stop
                                        </button>
                                    )}
                                    {isTerminal && (
                                        <span className="rounded-md bg-zinc-100 px-3 py-1.5 text-sm font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            {session.status_label}
                                        </span>
                                    )}
                                </div>

                                <SpeedControl
                                    sessionId={session.id}
                                    currentSpeed={session.speed_multiplier}
                                />
                            </div>

                            <div className="mt-3">
                                <div className="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>
                                        {session.current_event_index} / {session.total_events} events
                                    </span>
                                    <span>{progress}%</span>
                                </div>
                                <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div
                                        className="h-full rounded-full bg-blue-600 transition-all"
                                        style={{ width: `${progress}%` }}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="mt-4 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <EventLog
                                events={session.events}
                                currentIndex={session.current_event_index}
                                skippedEvents={session.skipped_events}
                            />
                        </div>

                        {!isTerminal && (
                            <div className="mt-4 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                                <InjectPanel
                                    sessionId={session.id}
                                    disabled={!canPause}
                                />
                            </div>
                        )}

                        {session.cloud_match_id && (
                            <p className="mt-4 text-center text-xs text-zinc-400 dark:text-zinc-500">
                                Cloud match:{' '}
                                <a
                                    href={`${cloudUrl}/matches/${session.cloud_match_id}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-blue-500 hover:underline"
                                >
                                    {session.cloud_match_id}
                                </a>
                            </p>
                        )}
                    </>
                )}
            </div>
        </div>
    )
}

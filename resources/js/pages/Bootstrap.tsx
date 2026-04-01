import { useForm } from '@inertiajs/react'
import { FormEvent } from 'react'

type Props = {
    cloudUrl: string
}

export default function Bootstrap({ cloudUrl }: Props) {
    const form = useForm({})

    function submit(e: FormEvent) {
        e.preventDefault()
        form.post('/bootstrap')
    }

    return (
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <div className="mx-auto max-w-lg px-6 py-16">
                <div className="flex items-center gap-3">
                    <img
                        src="/images/chukka-logo-light-200.png"
                        alt="Chukka"
                        className="h-10 w-auto dark:hidden"
                    />
                    <img
                        src="/images/chukka-logo-dark-200.png"
                        alt="Chukka"
                        className="hidden h-10 w-auto dark:block"
                    />
                    <div>
                        <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                            chukka-sim
                        </h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            First-time setup
                        </p>
                    </div>
                </div>

                <div className="mt-8 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 className="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                        Set up simulation data
                    </h2>
                    <p className="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        The simulator needs two fictional clubs, teams, and player rosters to exist in your{' '}
                        <a
                            href={cloudUrl}
                            className="font-medium text-blue-600 hover:underline dark:text-blue-400"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            chukka.app
                        </a>{' '}
                        account. This is a one-time setup.
                    </p>

                    <div className="mt-5 rounded-md border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                        <h3 className="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            The following will be created:
                        </h3>
                        <ul className="mt-2 space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400">
                            <li className="flex items-start gap-2">
                                <span className="mt-0.5 text-blue-500">&#x25cf;</span>
                                <span>
                                    <strong className="text-zinc-800 dark:text-zinc-200">Central Newcastle WPC</strong>{' '}
                                    &mdash; 1 team, 13 players
                                </span>
                            </li>
                            <li className="flex items-start gap-2">
                                <span className="mt-0.5 text-white dark:text-zinc-400">&#x25cf;</span>
                                <span>
                                    <strong className="text-zinc-800 dark:text-zinc-200">Easts WPC</strong>{' '}
                                    &mdash; 1 team, 13 players
                                </span>
                            </li>
                        </ul>
                        <p className="mt-3 text-xs text-zinc-500 dark:text-zinc-500">
                            You will be added as an admin of both clubs so the simulator can create matches between them.
                        </p>
                    </div>

                    <form onSubmit={submit} className="mt-6">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="w-full rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-300"
                        >
                            {form.processing ? 'Setting up...' : 'Set up simulation data'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    )
}

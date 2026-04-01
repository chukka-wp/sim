import { useForm } from '@inertiajs/react'
import { FormEvent } from 'react'

type Props = {
    cloudUrl: string
}

export default function Login({ cloudUrl }: Props) {
    const form = useForm({
        email: '',
        password: '',
    })

    function submit(e: FormEvent) {
        e.preventDefault()
        form.post('/login')
    }

    return (
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <div className="mx-auto max-w-sm px-6 py-24">
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
                            Match simulation tool
                        </p>
                    </div>
                </div>

                <div className="mt-8 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 className="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                        Sign in
                    </h2>
                    <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Use your{' '}
                        <a
                            href={cloudUrl}
                            className="font-medium text-blue-600 hover:underline dark:text-blue-400"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            chukka.app
                        </a>{' '}
                        account.
                    </p>

                    <form onSubmit={submit} className="mt-6 space-y-4">
                        <div>
                            <label
                                htmlFor="email"
                                className="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                required
                                autoFocus
                                autoComplete="email"
                                className="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            {form.errors.email && (
                                <p className="mt-1 text-sm text-red-600">{form.errors.email}</p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="password"
                                className="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={form.data.password}
                                onChange={(e) => form.setData('password', e.target.value)}
                                required
                                autoComplete="current-password"
                                className="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={form.processing}
                            className="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            {form.processing ? 'Signing in...' : 'Sign in'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    )
}

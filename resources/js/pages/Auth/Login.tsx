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
        <div className="flex min-h-svh">
            {/* Photo panel */}
            <div className="relative hidden w-1/2 lg:block">
                <picture>
                    <source srcSet="/img/auth-bg.webp" type="image/webp" />
                    <img
                        src="/img/auth-bg.jpg"
                        alt=""
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                </picture>
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-black/30" />
                <div className="absolute bottom-0 left-0 p-10">
                    <img
                        src="/img/logo-dark.png"
                        alt="Chukka"
                        className="h-12 w-auto"
                    />
                    <p className="mt-3 max-w-sm text-sm leading-relaxed text-white/80">
                        Live water polo scoring, broadcasting, and match management for clubs.
                    </p>
                </div>
            </div>

            {/* Form panel */}
            <div className="flex w-full flex-col items-center justify-center gap-6 bg-white p-6 md:p-10 lg:w-1/2 dark:bg-zinc-950">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <img
                                src="/images/chukka-logo-light-200.png"
                                alt="Chukka"
                                className="h-16 w-auto dark:hidden"
                            />
                            <img
                                src="/images/chukka-logo-dark-200.png"
                                alt="Chukka"
                                className="hidden h-16 w-auto dark:block"
                            />

                            <div className="space-y-2 text-center">
                                <h1 className="text-xl font-medium text-zinc-900 dark:text-zinc-100">
                                    Sign in to chukka-sim
                                </h1>
                                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                    Use your{' '}
                                    <a
                                        href={cloudUrl}
                                        className="font-medium text-blue-600 hover:underline dark:text-blue-400"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        chukka.app
                                    </a>{' '}
                                    account
                                </p>
                            </div>
                        </div>

                        <form onSubmit={submit} className="flex flex-col gap-6">
                            <div className="grid gap-6">
                                <div className="grid gap-2">
                                    <label
                                        htmlFor="email"
                                        className="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                    >
                                        Email address
                                    </label>
                                    <input
                                        id="email"
                                        type="email"
                                        value={form.data.email}
                                        onChange={(e) => form.setData('email', e.target.value)}
                                        required
                                        autoFocus
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                        className="flex h-9 w-full rounded-md border border-zinc-200 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-zinc-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 dark:border-zinc-800 dark:placeholder:text-zinc-500 dark:focus-visible:ring-zinc-300"
                                    />
                                    {form.errors.email && (
                                        <p className="text-sm text-red-600 dark:text-red-400">
                                            {form.errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="grid gap-2">
                                    <label
                                        htmlFor="password"
                                        className="text-sm font-medium text-zinc-700 dark:text-zinc-300"
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
                                        placeholder="Password"
                                        className="flex h-9 w-full rounded-md border border-zinc-200 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-zinc-400 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 dark:border-zinc-800 dark:placeholder:text-zinc-500 dark:focus-visible:ring-zinc-300"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-300"
                                >
                                    {form.processing ? 'Signing in...' : 'Sign in'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    )
}

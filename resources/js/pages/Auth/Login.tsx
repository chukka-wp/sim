import { Head } from '@inertiajs/react'

export default function Login({ idUrl }: { idUrl: string }) {
    return (
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <Head title="Log in" />

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
                        Sign in to continue
                    </h2>
                    <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Authenticate with your Chukka account
                    </p>

                    <div className="mt-6 space-y-4">
                        <a
                            href="/auth/redirect"
                            className="flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Sign in with Chukka ID
                        </a>

                        <p className="text-center text-sm text-zinc-500 dark:text-zinc-400">
                            Don't have an account?{' '}
                            <a
                                href={`${idUrl}/register`}
                                className="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400"
                            >
                                Create one on Chukka ID
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    )
}

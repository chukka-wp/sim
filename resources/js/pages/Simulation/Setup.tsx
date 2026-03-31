import { useForm } from '@inertiajs/react'
import { store } from '@/actions/App/Http/Controllers/SimulationController'
import type { ModelOption, RuleSet, ScenarioPreset } from '@/types/simulation'
import { FormEvent, useEffect } from 'react'

type Props = {
    ruleSets: RuleSet[]
    presets: ScenarioPreset[]
    models: ModelOption[]
}

export default function Setup({ ruleSets, presets, models }: Props) {
    const form = useForm({
        rule_set_id: ruleSets[0]?.id ?? '',
        scenario_preset: 'routine',
        scenario_prompt: presets.find((p) => p.key === 'routine')?.prompt ?? '',
        model_name: models[0]?.value ?? '',
        auto_play: false,
    })

    const scenarioPreset = form.data.scenario_preset

    useEffect(() => {
        const preset = presets.find((p) => p.key === scenarioPreset)
        if (preset && preset.key !== 'free_text') {
            form.setData('scenario_prompt', preset.prompt)
        }
    }, [scenarioPreset, presets])

    function submit(e: FormEvent, autoPlay: boolean) {
        e.preventDefault()
        form.transform((data) => ({ ...data, auto_play: autoPlay })).post(store.url())
    }

    return (
        <div className="min-h-screen bg-zinc-50 dark:bg-zinc-950">
            <div className="mx-auto max-w-2xl px-6 py-12">
                <h1 className="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                    chukka-sim
                </h1>
                <p className="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Match simulation tool
                </p>

                <div className="mt-8 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 className="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                        Match setup
                    </h2>

                    <form className="mt-6 space-y-5">
                        <div>
                            <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Rule set
                            </label>
                            <select
                                value={form.data.rule_set_id}
                                onChange={(e) => form.setData('rule_set_id', e.target.value)}
                                className="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                {ruleSets.map((rs) => (
                                    <option key={rs.id} value={rs.id}>
                                        {rs.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Model
                            </label>
                            <select
                                value={form.data.model_name}
                                onChange={(e) => form.setData('model_name', e.target.value)}
                                className="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                {models.map((m) => (
                                    <option key={m.value} value={m.value}>
                                        {m.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Scenario
                            </label>
                            <select
                                value={form.data.scenario_preset}
                                onChange={(e) => form.setData('scenario_preset', e.target.value)}
                                className="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            >
                                {presets.map((p) => (
                                    <option key={p.key} value={p.key}>
                                        {p.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Prompt
                            </label>
                            <textarea
                                value={form.data.scenario_prompt}
                                onChange={(e) => form.setData('scenario_prompt', e.target.value)}
                                rows={5}
                                className="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            {form.errors.generation && (
                                <p className="mt-1 text-sm text-red-600">{form.errors.generation}</p>
                            )}
                            {form.errors.cloud && (
                                <p className="mt-1 text-sm text-red-600">{form.errors.cloud}</p>
                            )}
                        </div>

                        <div className="flex gap-3 pt-2">
                            <button
                                type="submit"
                                disabled={form.processing}
                                onClick={(e) => submit(e, true)}
                                className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                {form.processing ? 'Generating...' : 'Generate & Play'}
                            </button>
                            <button
                                type="submit"
                                disabled={form.processing}
                                onClick={(e) => submit(e, false)}
                                className="rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                            >
                                Generate Only
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    )
}

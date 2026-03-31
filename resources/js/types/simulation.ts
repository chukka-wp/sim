export type RuleSet = {
    id: string
    name: string
    periods: number
    period_duration_seconds: number
    running_time: boolean
    possession_clock_enabled: boolean
}

export type SimulationStatus =
    | 'pending'
    | 'generating'
    | 'generated'
    | 'playing'
    | 'paused'
    | 'stopped'
    | 'completed'
    | 'failed'

export type SkippedEvent = {
    index: number
    type: string
    reason: string
}

export type SimulationEvent = {
    type: string
    period: number
    period_clock_seconds: number
    recorded_at: string
    payload: Record<string, unknown>
}

export type SimulationSessionData = {
    id: string
    cloud_match_id: string | null
    status: SimulationStatus
    status_label: string
    scenario_preset: string | null
    model_name: string
    speed_multiplier: number
    current_event_index: number
    total_events: number
    skipped_events: SkippedEvent[]
    last_event_at: string | null
    error_message: string | null
    events: SimulationEvent[]
    created_at: string
}

export type ScenarioPreset = {
    key: string
    label: string
    prompt: string
}

export type ModelOption = {
    value: string
    label: string
}

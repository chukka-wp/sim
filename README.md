# Chukka Sim

Match simulation tool for the [Chukka](https://github.com/chukka-wp/chukka) water polo platform. Uses an LLM (Prism PHP + Claude) to generate realistic event sequences and plays them into chukka-cloud at real-time speed.

## Features

- LLM-driven match event generation with realistic timing
- Configurable match parameters (teams, rule sets, play styles)
- Real-time playback into chukka-cloud via REST API
- Useful for testing, demos, and development

## Tech Stack

Laravel 13 / React 19 / Inertia 3 / TypeScript / Prism PHP

## Setup

```bash
composer setup    # install deps, generate key, migrate, build assets
composer dev      # start all services
```

## License

[MIT License](https://opensource.org/licenses/MIT)

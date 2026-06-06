# The One Fact

A tiny self-hosted app that surfaces **one fact a day from J.R.R. Tolkien's Legendarium**.

Every day an internal scheduler pulls a random article from
[Tolkien Gateway](https://tolkiengateway.net/wiki/Special:Random), asks an AI to distil a
single self-contained fact (plus a few tags) from it, and stores it in a SQLite database.
The newest fact is shown on a minimal web page and exposed as JSON.

- 🗓️ One fact per day, kept forever in SQLite
- 🤖 Bring-your-own AI — OpenAI, Anthropic, Gemini, Mistral, Groq, xAI, DeepSeek, Ollama… (via [`laravel/ai`](https://laravel.com/docs/ai))
- 🌐 Simple Tailwind page + JSON API, no JavaScript, no accounts
- 🐳 Single small Docker image with a built-in cron — no extra services

## Installation (Docker)

Create a directory for the app:

```bash
mkdir the-one-fact && cd the-one-fact
```

Create a `docker-compose.yml`:

```yaml
services:
  the-one-fact:
    image: mydnic/the-one-fact:latest
    restart: unless-stopped
    container_name: the-one-fact
    ports:
      - "8467:80"
    volumes:
      - ./data:/data            # SQLite database persistence (back this up)
    environment:
      - AI_PROVIDER=openai      # openai | anthropic | gemini | mistral | groq | xai | deepseek | ollama
      - AI_MODEL=               # optional model override, blank = provider default
      - OPENAI_API_KEY=sk-...   # the key matching your provider
```

Start it:

```bash
docker compose up -d
```

Open <http://localhost:8467>. Once the first fact has been generated it appears on the page
and at `GET /api/fact`.

All persistent data lives in the `./data` folder you mounted — back it up and you're safe.
Change the host port (`8467`) in `docker-compose.yml` if it clashes with something else.

### Choosing an AI provider

Set `AI_PROVIDER` and the matching API key. Examples:

| Provider  | `AI_PROVIDER` | Key variable        |
| --------- | ------------- | ------------------- |
| OpenAI    | `openai`      | `OPENAI_API_KEY`    |
| Anthropic | `anthropic`   | `ANTHROPIC_API_KEY` |
| Gemini    | `gemini`      | `GEMINI_API_KEY`    |
| Mistral   | `mistral`     | `MISTRAL_API_KEY`   |
| Groq      | `groq`        | `GROQ_API_KEY`      |
| xAI       | `xai`         | `XAI_API_KEY`       |
| DeepSeek  | `deepseek`    | `DEEPSEEK_API_KEY`  |
| Ollama    | `ollama`      | `OLLAMA_URL`        |

You never have to touch the code — everything is configured from `docker-compose.yml`.

## Generating a fact on demand

The container runs its own daily cron, but you can trigger a fresh fact at any time:

```bash
docker compose run --rm the-one-fact fact:generate
```

This stores (or refreshes) today's fact and prints it to the terminal.

## API

`GET /api/fact` returns the latest fact:

```json
{
  "data": {
    "date": "2026-06-05",
    "title": "The Twice-Born Elf",
    "fact": "Glorfindel died slaying a Balrog during the Fall of Gondolin and was later re-embodied in Valinor.",
    "tags": ["Glorfindel", "Gondolin", "Balrogs"],
    "source": {
      "title": "Glorfindel",
      "url": "https://tolkiengateway.net/wiki/Glorfindel"
    }
  }
}
```

## Local development

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate

# Generate a fact (needs a real AI key in .env), or run the test suite:
php artisan fact:generate
php artisan test

# Serve the front-end assets while developing:
npm run dev
```

## How it works

| Piece                                   | Responsibility                                            |
| --------------------------------------- | --------------------------------------------------------- |
| `App\Services\TolkienGateway`           | Fetches a random wiki page and extracts the article text. |
| `App\Ai\Agents\FactExtractor`           | `laravel/ai` agent returning structured `{title, fact, tags}`. |
| `App\Jobs\GenerateDailyFact`            | Orchestrates fetch → AI → store as today's fact.          |
| `fact:generate` command                 | Runs the job (used by the scheduler and on-demand).       |
| `routes/console.php`                    | Schedules `fact:generate` daily at 06:00.                 |
| `App\Http\Controllers\FactController`   | Serves the web page and the JSON API.                     |

## License

MIT.

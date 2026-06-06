<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fact?->title ? $fact->title.' — The One Fact' : 'The One Fact' }}</title>
    <meta name="description" content="A daily fact from J.R.R. Tolkien's Legendarium.">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-stone-950 text-stone-100 selection:bg-amber-400/30">
    <div class="relative min-h-screen overflow-hidden">
        {{-- Ambient glow --}}
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(60rem_40rem_at_50%_-10%,rgba(245,158,11,0.15),transparent)]"></div>

        <main class="relative mx-auto flex min-h-screen max-w-3xl flex-col justify-center px-6 py-16">
            <header class="mb-10 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-400/80">The One Fact</p>
                <p class="mt-2 text-sm text-stone-400">A daily fact from Tolkien's Legendarium</p>
            </header>

            @if ($fact)
                <article class="rounded-3xl border border-stone-800/80 bg-stone-900/60 p-8 shadow-2xl shadow-black/40 backdrop-blur sm:p-12">
                    <time datetime="{{ $fact->fact_date->toDateString() }}" class="block text-xs font-medium uppercase tracking-widest text-stone-500">
                        {{ $fact->fact_date->format('F j, Y') }}
                    </time>

                    <h1 class="mt-4 font-serif text-3xl font-semibold leading-tight text-amber-100 sm:text-4xl">
                        {{ $fact->title }}
                    </h1>

                    <p class="mt-6 text-lg leading-relaxed text-stone-200 sm:text-xl">
                        {{ $fact->content }}
                    </p>

                    @if (! empty($fact->tags))
                        <ul class="mt-8 flex flex-wrap gap-2">
                            @foreach ($fact->tags as $tag)
                                <li class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-sm text-amber-200">
                                    {{ $tag }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <footer class="mt-10 border-t border-stone-800 pt-6 text-sm text-stone-500">
                        Source:
                        <a href="{{ $fact->source_url }}" rel="noopener noreferrer"
                           class="text-stone-300 underline decoration-stone-600 underline-offset-4 transition hover:text-amber-300">
                            {{ $fact->source_title ?? $fact->source_url }}
                        </a>
                    </footer>
                </article>
            @else
                <article class="rounded-3xl border border-stone-800/80 bg-stone-900/60 p-12 text-center shadow-2xl shadow-black/40 backdrop-blur">
                    <h1 class="font-serif text-2xl font-semibold text-amber-100">The tale has not yet begun</h1>
                    <p class="mt-4 text-stone-300">No fact has been generated yet. The first one will appear after the daily task runs.</p>
                    <p class="mt-2 text-sm text-stone-500">You can trigger it now with <code class="rounded bg-stone-800 px-1.5 py-0.5 text-amber-200">php artisan fact:generate</code>.</p>
                </article>
            @endif

            <footer class="mt-8 flex items-center justify-center gap-3 text-center text-xs text-stone-600">
                <a href="{{ route('fact.json') }}" class="underline decoration-stone-700 underline-offset-4 transition hover:text-stone-400">JSON API</a>
                <span class="text-stone-700">·</span>
                <a href="{{ config('thefact.github_url') }}" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1 underline decoration-stone-700 underline-offset-4 transition hover:text-stone-400">
                    <svg viewBox="0 0 16 16" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                        <path d="M8 0C3.58 0 0 3.58 0 8a8 8 0 0 0 5.47 7.59c.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8 8 0 0 0 16 8c0-4.42-3.58-8-8-8Z"/>
                    </svg>
                    Deploy your own
                </a>
            </footer>
        </main>
    </div>
</body>
</html>

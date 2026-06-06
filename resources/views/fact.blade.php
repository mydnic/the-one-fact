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

            <footer class="mt-8 text-center text-xs text-stone-600">
                <a href="{{ route('fact.json') }}" class="underline decoration-stone-700 underline-offset-4 transition hover:text-stone-400">JSON API</a>
            </footer>
        </main>
    </div>
</body>
</html>

<?php

namespace App\Console\Commands;

use App\Jobs\GenerateDailyFact;
use App\Models\Fact;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('fact:generate {--queue : Dispatch the job onto the queue instead of running it now}')]
#[Description("Fetch a random Tolkien Gateway article and store today's fact")]
class GenerateFact extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('queue')) {
            GenerateDailyFact::dispatch();
            $this->info('Queued daily fact generation.');

            return self::SUCCESS;
        }

        try {
            dispatch_sync(new GenerateDailyFact);
        } catch (Throwable $e) {
            $this->error('Failed to generate a fact: '.$e->getMessage());

            return self::FAILURE;
        }

        $fact = Fact::ofTheDay();

        $this->newLine();
        $this->line('  <fg=yellow;options=bold>'.$fact->title.'</>');
        $this->line('  '.$fact->content);
        $this->line('  <fg=gray>'.implode(' · ', $fact->tags).'</>');
        $this->line('  <fg=gray>Source: '.$fact->source_url.'</>');
        $this->newLine();

        return self::SUCCESS;
    }
}

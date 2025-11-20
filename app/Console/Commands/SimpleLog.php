<?php

namespace App\Console\Commands;

use App\Events\SimpleLogEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SimpleLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simple-log --{par1=24}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $lock = Cache::lock('simple-log-command-lock', 10);

        // if (!$lock->get()) {
        //     $this->error('You are running this command too frequently. Try again in about a minute.');
        //     return Command::FAILURE;
        // }

        try {
            $par1 = $this->argument('par1');
            Log::info("Params", ["Param1" => $par1]);
            event(new SimpleLogEvent());

            $this->info("SimpleLog executed successfully.");
        } finally {
            // $lock->release();
        }

        return Command::SUCCESS;
    }
}

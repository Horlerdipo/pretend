<?php

namespace Horlerdipo\Pretend\Commands;

use Illuminate\Console\Command;

class PretendCommand extends Command
{
    public $signature = 'pretend';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}

<?php

namespace Sync\Commands;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NowTime extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf("Now time: %s", Carbon::now()->isoFormat('HH:mm (DD.YYYY)')));

        return 0;
    }
}

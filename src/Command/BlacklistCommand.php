<?php

namespace App\Command;

use App\Repository\BlacklistRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'blacklist',
    description: 'Manage blacklisted host records',
)]
class BlacklistCommand extends Command
{
    private $blacklistRepository;
    public function __construct(BlacklistRepository $blacklistRepository)
    {
        parent::__construct();
        $this->blacklistRepository = $blacklistRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $records=$this->blacklistRepository->findAll();
        foreach($records as $record){
            //dd($record->host);
            echo $record->getHost()."\n";
        }

        //$io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        return Command::SUCCESS;
    }
}

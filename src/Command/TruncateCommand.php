<?php

namespace App\Command;

use Doctrine\DBAL\Connection;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'truncate',
    description: 'TRUNCATE TABLE link',
)]
class TruncateCommand extends Command
{
    private $connection;

    
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct();
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
        
        $conf=$io->confirm("You are about to TRUNCATE the link table. Confirm ?", false);
        
        if ($conf) {
            $sql="TRUNCATE TABLE link;";
            $this->connection->fetchAllAssociative($sql);
            $io->success('You got some balls!');
            $io->info("maybe its time to run : php bin/console sync ?");
        }
    
        return Command::SUCCESS;
    }
}

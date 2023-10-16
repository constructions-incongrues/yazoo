<?php

namespace App\Command;

use App\Repository\SearchRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'search',
    description: 'Add a short description for your command',
)]
class SearchCommand extends Command
{

    private $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        parent::__construct();

        $this->searchRepository=$searchRepository;
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

        $io->success('Search: '.$arg1);

        $items=$this->searchRepository->search($arg1, 1, 30);
        //print_r($items);

        foreach($items['results'] as $item){
            //print_r($item);exit;
            echo $item['url']."\t";
            echo "[".$item['status']."]\n";
        }

        return Command::SUCCESS;
    }
}

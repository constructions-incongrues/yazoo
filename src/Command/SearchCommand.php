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
    description: 'Perform a Yazoo search on the command line',
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
            //ok
        }else{
            $io->error("You must pass a search query");
            $io->comment('Example : $./bin/console search status:200');
            $io->comment('     or : $./bin/console search "tintin et milou"');
            return Command::SUCCESS;
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('Search: '.$arg1);

        $this->searchRepository->search($arg1);
        //$this->searchRepository->debug();

        $data=$this->searchRepository->getResultPage(1,10);

        //dd($data['count']);
        //print_r($items);

        foreach($data['results'] as $item){
            //dd($item);
            //print_r($item);exit;
            echo '['.$item->getStatus()."]\t";
            if($item->getVisitedAt()){
                echo $item->getVisitedAt()->format("Y-m-d")."\t";
            }else{
                echo "not visited\t";
            }

            echo $item->getUrl()."\t";
            //echo "[".$item->ge."]\n";
            echo "\n";
        }

        return Command::SUCCESS;
    }
}

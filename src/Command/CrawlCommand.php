<?php
/**
 * General purpose crawler.
 * Test/Debug only ?
 * Real crawler are triggered by API endpoints : /job/crawl or /job/youtube etc
 */
namespace App\Command;

use App\Repository\BlacklistRepository;
use App\Repository\LinkRepository;
use App\Repository\SearchRepository;
use App\Service\CrawlService;
use App\Service\HttpStatusService;

use Embed\Embed;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'crawl',
    description: 'Crawl URLs with no http status',
)]
class CrawlCommand extends Command
{

    private $searchRepository;
    private $crawlService;


    public function __construct(SearchRepository $searchRepository, CrawlService $crawlService)
    {
        parent::__construct();

        $this->searchRepository=$searchRepository;

        $this->crawlService=$crawlService;

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
            //$io->note(sprintf('You passed an argument: %s', $arg1));
        }else{
            $io->info('You must passed an argument: {search}');
            //return Command::FAILURE;
            $arg1="orderby:crawler";
        }

        if ($input->getOption('option1')) {
            $io->note(sprintf('OPTION: %s', $arg1));
        }


        $httpstatusservice=new HttpStatusService();


        while($this->searchRepository->search($arg1)){
            //$this->searchRepository->filterUnreachable();//unreachable ONly
            $data=$this->searchRepository->getResultPage(1,10);

            $links=$data['results'];

            $internet=$httpstatusservice->isInternetAvailable();

            if (!$internet) {
                $io->error("No internet");
                return Command::FAILURE;
            }
            //print_r($links);exit;

            //TODO:: Use CrawlService in the loop
            foreach($links as $link){
                //echo $link->getId()."\t";
                //echo $link->getUrl()."\n";

                $crawled=$this->crawlService->crawlLink($link);

                if (!$crawled) {
                    echo "?\n";
                    continue;
                }

                echo "#".$crawled->getId();
                echo "\t";
                echo $crawled->getStatus();
                echo "\t";
                echo $crawled->getUrl();
                echo "\n";
            }
        }



        $io->success("Done");

        return Command::SUCCESS;
    }
}

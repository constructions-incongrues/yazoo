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
    name: 'checkblacklist',
    description: 'Add a short description for your command',
)]
class CheckblacklistCommand extends Command
{
    private $blacklistRepository;
    public function __construct(BlacklistRepository $blacklistRepository)
    {
        parent::__construct();
        $this->blacklistRepository=$blacklistRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    function testDNSRecord(string $url) {

        $ipAddress = gethostbyname($url);
        echo "$ipAddress\n";
        /*
        if ($ipAddress == $url) {
            return "DNS record not found for the URL.";
        } else {
            return "IP Address for the URL: " . $ipAddress;
        }
        */
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

        $items=$this->blacklistRepository->findAll();
        foreach($items as $item){
            $host=$item->getHost();
            echo "$host\n";
            $ips=gethostbynamel($host);
            if (!$ips) {
                $io->error("No IPv4");
                $item->setComment("No IPv4");
                $item->setUpdated();
                $this->blacklistRepository->save($item,true);
            }else{
                print_r($ips);
            }
            //$dns=checkdnsrr($host);
            //print_r($dns);

            //$this->testDNSRecord($url);
        }

        $io->success('done.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Repository\LinkRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'import',
    description: 'Add a short description for your command',
)]
class ImportCommand extends Command
{
    private $linkRepository;

    public function __construct(LinkRepository $linkRepository)
    {
        parent::__construct();
        $this->linkRepository=$linkRepository;
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
            if (is_file($arg1)) {
                //
            }else{
                $io->error("no file like $arg1");
            }
        }else{
            $io->error("set filename");
            return Command::FAILURE;
        }

        $rows=file($arg1);

        foreach ($rows as $row) {
            if(!preg_match("/^http/",$row))continue;
            $url=trim($row);
            $link=$this->linkRepository->saveUrl($url);
            echo $link->getId();
            echo "\t";
            echo $link->getUrl();
            echo "\n";
            //$io->success($url);
            //dd($link);
        }


        $io->success('Done');

        return Command::SUCCESS;
    }
}

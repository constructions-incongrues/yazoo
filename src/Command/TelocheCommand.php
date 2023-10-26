<?php

namespace App\Command;

use App\Service\TelocheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'teloche',
    description: 'Add a short description for your command',
)]
class TelocheCommand extends Command
{

    private $telocheService;
    public function __construct(TelocheService $telocheService)
    {
        parent::__construct();
        $this->telocheService=$telocheService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            //->addArgument('import', InputArgument::OPTIONAL, 'Argument description')
            //->addOption('url', null, InputOption::VALUE_REQUIRED, 'Video URL')
            ->addOption('channel', 'c', InputOption::VALUE_REQUIRED, 'Set Channel id (INT)')
            ->addOption('import', 'i', InputOption::VALUE_REQUIRED, 'Import URL')
            ->addOption('list-channels', null, InputOption::VALUE_NONE, 'List Teloche Channels')
            ->addOption('list-videos', null, InputOption::VALUE_NONE, 'List Videos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');



        $channel = $input->getOption('channel');
        if ($channel) {
            $io->success('Channel is #'.$channel);
        }

        if ($url=$input->getOption('import')) {
            $io->comment('Import URL:'.$url);
            if (!$channel) {
                $io->error('Set Video channel with --channel');
                return Command::FAILURE;
            }
            $this->telocheService->authenticate();
            $this->telocheService->importVideo($url, $channel);
            $io->success('Done!');
            return Command::SUCCESS;
        }


        if ($input->getOption('list-channels')) {
            $io->success('list-channels');
            $json=$this->telocheService->listVideoChannels();
            $data=$json['data'];
            foreach($data as $channel){
                //var_dump($channel);exit;
                echo '#'.$channel['id'];
                echo "\t";
                echo $channel['name'];
                echo "\n";
            }
        }

        if ($input->getOption('list-videos')) {
            $io->success('list-videos');
            $json=$this->telocheService->listVideos();
            $data=$json['data'];
            foreach($data as $video){
                var_dump($video);exit;

                echo "\n";
            }
        }

        return Command::SUCCESS;
    }
}

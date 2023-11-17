<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Embed\Embed;
use Exception;


#[AsCommand(
    name: 'urlinfo',
    description: 'Add a short description for your command',
)]
class UrlinfoCommand extends Command
{
    public function __construct()
    {
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
        } else {
            $io->error('You must pass an argument: URL');
            return Command::FAILURE;
        }

        if ($input->getOption('option1')) {
            // ...
        }



        try{
            $embed = new Embed();
            $info=$embed->get($arg1);

            $dat['title']=(string)$info->title;
            $dat['description']=(string)$info->description;
            $dat['image']=(string)$info->image;
            $dat['code']=(string)$info->code;
            $dat['statusCode']=$info->getResponse()->getStatusCode();
        }

        catch(Exception $e){
            $dat['error']=$e->getMessage();
            $dat['errorCode']=$e->getCode();
        }

        print_r($dat);
        //$io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        return Command::SUCCESS;
    }
}

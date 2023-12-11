<?php

namespace App\Command;

use App\Repository\DiscussionRepository;
use App\Repository\LinkRepository;
use App\Service\ExtractService;
use App\Service\MusiqueIncongrueService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sync',
    description: 'Syncronise forum links',
)]
class SyncCommand extends Command
{
    private $musiqueIncongrueService;
    private $extractService;
    private $linkRepository;
    private $discussionRepository;

    public function __construct(MusiqueIncongrueService $musiqueIncongrueService, ExtractService $extractService, LinkRepository  $linkRepository, DiscussionRepository $discussionRepository)
    {
        parent::__construct();
        $this->musiqueIncongrueService = $musiqueIncongrueService;
        $this->extractService = $extractService;
        $this->linkRepository= $linkRepository;
        $this->discussionRepository=$discussionRepository;
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

        $dat=[];
        $dat['start_time']=time();

        // 0 - Authenticate
        $token=$this->musiqueIncongrueService->authenticate();

        if (!$token) {
            $io->error('No token');
            return Command::FAILURE;
        }

        // 1 - Sync Links - Fetch From Directus/MusiqueIncongrues
        $data=$this->musiqueIncongrueService->fetchComments();

        foreach($data['data'] as $r){
            $r['urls']=$this->extractService->extractUrls($r['Body']);

            if (count($r['urls'])) {
                $this->linkRepository->saveUrls($r['urls'], $r['CommentID'], $r['DiscussionID'], $r['AuthUserID']);
                print_r($r['urls']);
            }

        }

        // 2 - Sync Discussions
        $discussions=$this->musiqueIncongrueService->fetchDiscussions();//Fetch From Directus/MusiqueIncongrues
        foreach($discussions['data'] as $discussion){
            $this->discussionRepository->saveDiscussion($discussion['DiscussionID'], $discussion['Name'], $discussion['DateCreated']);
            $io->comment("New discussion: " . $discussion['Name']);
        }

        $dat['exec_time']=time()-$dat['start_time'];

        $io->success(sprintf("done in %s seconds", $dat['exec_time']));

        return Command::SUCCESS;
    }
}

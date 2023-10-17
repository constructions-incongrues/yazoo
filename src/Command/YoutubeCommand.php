<?php

namespace App\Command;

use App\Repository\LinkRepository;
use App\Service\YoutubeService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'youtube',
    description: 'Add a short description for your command',
)]
class YoutubeCommand extends Command
{
    private $linkRepository;
    private $youtubeService;

    public function __construct(LinkRepository $linkRepository, YoutubeService $youtubeService)
    {
        parent::__construct();
        $this->linkRepository=$linkRepository;
        $this->youtubeService=$youtubeService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    public function removeInvalidUTF8Characters($text) {
        // Match only valid UTF-8 characters
        $pattern = '/[\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';
        return preg_replace($pattern, '', $text);
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

        //Get Youtube Videos with no status
        $links=$this->linkRepository->findByProvider('Youtube', 30);
        foreach($links as $link){
            $url=$link->getUrl();
            echo "#".$link->getId();
            echo "\t$url\t";

            $key=$this->youtubeService->url2key($url);//extract youtube videoID

            if (!$key) {
                echo "SKIP\n";
                continue;
            }

            //$key="fvUJyKxC9uw";//not available

            $data=$this->youtubeService->fetch2($key);
            //dd($data);
            //echo count($data['items']);
            if ($data['items']&&count($data['items'])>0) { //Got VIDEO

                $items=$data['items'][0]['snippet'];
                //dd($items);
                $title=$items['title'];
                echo "$title\n";
                //$description=$items['description'];
                //dd($items);
                $link->setTitle($this->removeInvalidUTF8Characters($items['title']));
                $link->setDescription($this->removeInvalidUTF8Characters($items['description']));
                $thumbs=$items['thumbnails'];

                //image
                if( $thumbs['default']) {
                    $link->setImage($thumbs['default']["url"]);
                }
                $link->setStatus(200);//ok
            } else {
                echo "[404]\n";
                $link->setTitle('Video unavailable');
                $link->setStatus(404);//not found
            }

            $this->linkRepository->save($link,true);
            exit;//Now make sure its SAVED !!


        }

        $io->success('done');

        return Command::SUCCESS;
    }
}

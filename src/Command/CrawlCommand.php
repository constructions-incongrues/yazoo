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

    private $linkrepo;
    private $searchRepository;

    private $blacklistRepository;

    private $logger;

    private $extractService;

    public function __construct(LinkRepository $linkrepo, LoggerInterface $logger, SearchRepository $searchRepository, BlacklistRepository $blacklistRepository)
    {
        parent::__construct();
        $this->linkrepo=$linkrepo;
        $this->searchRepository=$searchRepository;
        $this->blacklistRepository=$blacklistRepository;
        $this->logger=$logger;
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


        $httpstatusservice=new HttpStatusService();

        //while($links=$this->linkrepo->findWhereStatusIsNull()){
        //while($links=$this->linkrepo->findWaitingProvider('Myspace',10)){
        //while($links=$this->linkrepo->findWaitingImages(10)){
        while($data=$this->searchRepository->search($arg1)){
            $links=$data['results'];

            //print_r($links);exit;

            foreach($links as $link){
                //dd($link);
                $url=$link->getUrl();

                //Youtube Got a dedicated Crawler
                if (preg_match("/(youtube\.com|youtu\.be)/",$url)) {
                    continue;
                }

                //Check against blacklist
                if ($this->blacklistRepository->isBlacklisted($url)) {
                    $io->error("$url is blacklisted");
                    $this->linkrepo->delete($link);
                    continue;
                }

                echo "getHttpCode($url)\n";

                $status=$httpstatusservice->get($url);
                //print_r($status);exit;
                echo "$url\t [".$status['httpStatus']."] ".$status['info']."\n";
                $link->setStatus($status['httpStatus']);
                $link->setMimetype($status['mimeType']);

                if ($status['httpStatus']==0) {//Unreachable
                    $this->logger->warning("Unreachable URL",['channel'=>'crawler', 'url'=>$url]);//
                    //$this->logger->notice("Unreachable URL",['url'=>$url]);
                    $parse = parse_url($url);
                    $host=$parse['host'];
                    //$this->blacklistRepository->add($host);//too harsh
                    continue;
                }

                if ($status['httpStatus']>=200&&$status['httpStatus']<400) {

                    if (preg_match("/\.imageshack\.us/",$url)) {
                        //ImageShack Adapter is broken !
                        //http://img17.imageshack.us/i/monaunvarnishwebimage.jpg
                        //'http://img37.imageshack.us/i/mpparvous.jpg'
                        continue;
                    }

                    try{
                        $embed = new Embed();
                        $info=$embed->get($url);
                    }

                    catch(Exception $e){
                        $io->error($e->getMessage());
                        continue;
                    }


                    $meta=[];
                    if($info->title){
                        $meta['title']=$info->title; //The page title
                        $link->setTitle($info->title);
                    }else{
                        $link->setTitle(basename($url));
                    }

                    $meta['description']=$info->description; //The page description
                    $link->setDescription($info->description);
                    $meta['canonical']=(string)$info->url; //The canonical url
                    $link->setCanonical($info->url);
                    //$meta['keywords']=$info->keywords; //The page keywords
                    //$meta['image']=(string)$info->image;
                    if ($info->image) {
                        //TODO check URL length and content
                        //$link->setImage($info->image);
                    }

                    //$meta['lang']=$info->language; //The language of the page
                    $meta['provider']=$info->providerName; //The provider name of the page (Youtube, Twitter, Instagram, etc)
                    $link->setProvider($info->providerName);

                    print_r($meta);

                    //Fix 301 that are 404
                    //Todo -> make a factory about it
                    if (preg_match("/\b(404|page not found)\b/i",$link->getTitle())) {
                        $io->warning("404 detected in title : ".$link->getTitle());
                        $link->setStatus(404);
                    }

                }
                $link->visited();
                $this->linkrepo->save($link,true);
            }
        }



        $io->success("Done");

        return Command::SUCCESS;
    }
}

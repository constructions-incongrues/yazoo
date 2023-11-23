<?php
/**
 * This command scrap TheBrainRadioshow Releases, and extract the youtube video URLS !
 */
namespace App\Command;

use Embed\Embed;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'thebrain',
    description: 'Extract Thebrainradio URLs (Episodes or Videos)',
)]
class ThebrainCommand extends Command
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

        $urls=[];

        for($i=36; $i<=195; $i++){
            $url='https://www.thebrainradio.com/listen.php?episode=' . $i;
            //https://packagist.org/packages/embed/embed
            $embed = new Embed();
            echo "$url\n";
            $info=$embed->get($url);

            //Get the document object
            $document = $info->getDocument();
            $html = (string) $document; //Returns the html code

            $dom = new \DOMDocument;
            $dom->loadHTML($html);

            $iframes = $dom->getElementsByTagName('iframe');

            foreach ($iframes as $iframe) {
                $src = $iframe->getAttribute('src');
                echo "$src\n";
                $urls[]=$src;
            }
            //exit;
            //dd($info);
            sleep(1);
        }

        //var_dump($urls);

        $f=fopen("/tmp/brain.txt","w+");
        fwrite($f,implode("\n", $urls));
        fclose($f);

        $io->success('done');

        return Command::SUCCESS;
    }
}

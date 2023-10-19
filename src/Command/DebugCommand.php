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
    name: 'debug',
    description: 'Add a short description for your command',
)]
class DebugCommand extends Command
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
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $link=$this->linkRepository->find(1);

        $crap='۝ 𝐋𝐚 𝐏𝐞𝐚𝐮 𝐃𝐨𝐮𝐜𝐞 - 𝐏𝐨𝐞́𝐬𝐢𝐞 𝐀𝐜𝐢𝐝 (𝐭𝐞𝐚𝐬𝐞𝐫) www.marguerin.net ۝ « C\'est vrai ? Ce que l\'on dit sur les canopées ? Qu\'elles communiquent comme on croyait seul les humains capables de chanter ? C\'est vrai ? Tout est vrai. Rien n\'est inventé. Les fantômes existent, ils ont une matière, les fantômes pissent, les fantômes se nourrissent de la sexualité. » ۝ 𝐋𝐚 𝐏𝐞𝐚𝐮 𝐃𝐨𝐮𝐜𝐞 est un concert acid poésie. Une embardée fantastique et érotique, entre le conte pour adultes et le spoken word techno. On s\'y promène dans la sexualité comme dans un manoir hanté, comme dans le labyrinthe de Shinning ou les couloirs du Nostromo. Urine verte, extra terrestres. Électro Noise. Occultisme et uranisme. ۝ 𝐌𝐮𝐬𝐢𝐪𝐮𝐞 : Clem Zancanaro 𝐓𝐞𝐱𝐭𝐞 𝐞𝐭 𝐯𝐨𝐢𝐱 : Marguerin 𝐕𝐢𝐝𝐞́𝐨 : Mateo Henot Juin 2022 ۝ 𝐃𝐚𝐭𝐞𝐬 𝐏𝐚𝐬𝐬𝐞́𝐞𝐬 25 juin 22 - L\'Embobineuse (Marseille) 24 juin 22 - Les Clameurs (Lyon) 23 juin 22 - SLP (Metz) 18 juin 22 - LDMNT DR (Strasbourg) 16 juin 22 - LaMutinerie (Paris) 12 mars 22 - La Centrale (Lyon) 25 février 22 -Mimesis (Villeurbanne) 18 décembre 21 - Le Bac à Sable (LaMulatière) 13 novembre 21 - Grrrnd Zéro (Vaulx-en-Velin) ۝ 𝐂𝐨𝐧𝐭𝐚𝐜𝐭 : jaimetout@protonmail.com';

        $convertedString = iconv('UTF-8', 'ASCII//TRANSLIT', $crap);

        $link->setDescription($convertedString);

        $io->comment($convertedString);

        $this->linkRepository->save($link,true);

        $io->comment('ok');

        return Command::SUCCESS;
    }
}

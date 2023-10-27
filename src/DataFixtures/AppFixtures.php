<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\Blacklist;
use Exception;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $filename=__DIR__ . '/../../data/host-blacklist.txt';
        /*
        if (!is_readable($filename)) {
            throw new Exception("$filename is unreadable", 1);
        }
        */
        $rows = file($filename);
        foreach($rows as $row){
            $host=trim($row);
            if(!$host)continue;
            $blacklist = new Blacklist();
            $blacklist->setHost(trim($host));
            $manager->persist($blacklist);
        }

        $manager->flush();
    }
}

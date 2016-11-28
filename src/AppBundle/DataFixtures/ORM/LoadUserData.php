<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $davi = new User();
        $davi->setName('Davi Vidal');
        $davi->setLogin('davi');
        $davi->setPassword($davi->encryptPassword('teste'));
        $davi->setBalance('100.00');

        $manager->persist($davi);

        $marcelo = new User();
        $marcelo->setName('Marcelo Ribeiro');
        $marcelo->setLogin('marcelo');
        $marcelo->setPassword($marcelo->encryptPassword('teste'));
        $marcelo->setBalance('100.00');

        $manager->persist($marcelo);

        $vinicius = new User();
        $vinicius->setName('Vinicius Trainotti');
        $vinicius->setLogin('vinicius');
        $vinicius->setPassword($vinicius->encryptPassword('teste'));
        $vinicius->setBalance('100.00');

        $manager->persist($vinicius);

        $manager->flush();
    }
}

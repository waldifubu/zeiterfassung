<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DefaultFixtures extends Fixture
{
    public function load(ObjectManager $manager):void
    {
        $project = new Project();
        $project->setName('Default');
        $manager->persist($project);

        $manager->flush();
    }
}

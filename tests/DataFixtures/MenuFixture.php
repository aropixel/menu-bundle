<?php

namespace Aropixel\MenuBundle\Tests\DataFixtures;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Tests\DataFixtures\PageFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        $deps = [PageFixture::class];

        if (class_exists('App\DataFixtures\ProjectFixture')) {
            $deps[] = 'App\DataFixtures\ProjectFixture';
        }

        return $deps;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createMainMenu($manager);
        $this->createFooterMenu($manager);

        $manager->flush();
    }

    private function createMainMenu(ObjectManager $manager): void
    {
        $root = $this->createItem('Navigation principale', 'main');
        $manager->persist($root);

        $home = $this->createItem('Accueil', 'main', '/');
        $home->setParent($root);
        $manager->persist($home);

        $about = $this->createItem('À propos', 'main', '/a-propos');
        $about->setParent($root);
        $manager->persist($about);

        $services = $this->createItem('Services', 'main', '/services');
        $services->setParent($root);
        $manager->persist($services);

        $offers = $this->createItem('Nos offres', 'main', '/services/nos-offres');
        $offers->setParent($services);
        $manager->persist($offers);

        $projectLink = '1';
        if (class_exists('App\Entity\Project') && $this->hasReference('project-1', \App\Entity\Project::class)) {
            /** @var \App\Entity\Project $project */
            $project = $this->getReference('project-1', \App\Entity\Project::class);
            $projectLink = (string) $project->getId();
        }

        $portfolio = $this->createItem('Réalisations', 'main', $projectLink);
        $portfolio->setType('project');
        $portfolio->setParent($root);
        $manager->persist($portfolio);

        $contact = $this->createItem('Contact', 'main', '/contact');
        $contact->setParent($root);
        $manager->persist($contact);
    }

    private function createFooterMenu(ObjectManager $manager): void
    {
        $root = $this->createItem('Footer', 'footer');
        $manager->persist($root);

        $about = $this->createItem('À propos', 'footer', '/a-propos');
        $about->setParent($root);
        $manager->persist($about);

        $services = $this->createItem('Services', 'footer', '/services');
        $services->setParent($root);
        $manager->persist($services);

        $contact = $this->createItem('Contact', 'footer', '/contact');
        $contact->setParent($root);
        $manager->persist($contact);
    }

    private function createItem(string $title, string $type, ?string $link = null): Menu
    {
        $item = new Menu();
        $item->setTitle($title);
        $item->setType($type);

        if (null !== $link) {
            $item->setLink($link);
        }

        return $item;
    }
}

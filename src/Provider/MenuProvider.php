<?php

namespace Aropixel\MenuBundle\Provider;

use Aropixel\MenuBundle\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsAlias(MenuProviderInterface::class)]
class MenuProvider implements MenuProviderInterface
{
    public const CACHE_KEY = '_aropixel.cache.menus';

    protected ?array $menus = null;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterBagInterface $parameterBag
    ) {
    }

    protected function loadMenus(): void
    {
        $cacheDuration = $this->parameterBag->get('aropixel_menu.cache.duration');

        if ($cacheDuration) {
            $this->loadAndCache($cacheDuration);
        } else {
            $this->load();
        }
    }

    protected function load(): void
    {
        $menuEntity = $this->parameterBag->get('aropixel_menu.entity');
        $this->menus = $this->em->getRepository($menuEntity)->findRootsWithPage();
        $this->splitMenus();
    }

    protected function loadAndCache($cacheDuration): void
    {
        $em = $this->em;
        $menuEntity = $this->parameterBag->get('aropixel_menu.entity');

        $cache = new FilesystemAdapter();
        $this->menus = $cache->get(self::CACHE_KEY, function (ItemInterface $item) use ($em, $menuEntity, $cacheDuration) {
            $menuItems = $em->getRepository($menuEntity)->findRootsWithPage();
            foreach ($menuItems as $menuItem) {
                $this->hydratePage($menuItem);
            }

            $item->expiresAfter($cacheDuration);
            $item->set($menuItems);

            return $menuItems;
        });

        $this->splitMenus();
    }

    protected function hydratePage(Menu $menuItem): void
    {
        $menuItem->getPage() && $menuItem->getPage()->getSlug();

        foreach ($menuItem->getChildren() as $child) {
            $this->hydratePage($child);
        }
    }

    protected function splitMenus(): void
    {
        $splittedMenus = [];

        /** @var Menu $menuItem */
        foreach ($this->menus as $menuItem) {
            $splittedMenus[$menuItem->getType()][] = $menuItem;
        }

        $this->menus = $splittedMenus;
    }

    public function getMenu($type): array
    {
        if (null === $this->menus) {
            $this->loadMenus();
        }

        return \array_key_exists($type, $this->menus) ? $this->menus[$type] : [];
    }

    public function refreshCache(): void
    {
        $cache = new FilesystemAdapter();
        $cache->delete(self::CACHE_KEY);

        $this->loadMenus();
    }
}

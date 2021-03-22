<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 21/05/2020 à 14:57
 */

namespace Aropixel\MenuBundle\Provider;


use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Entity\MenuInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MenuProvider implements MenuProviderInterface
{

    /** @var EntityManagerInterface */
    protected $em;


    /** @var ParameterBagInterface */
    protected $parameterBag;


    /** @var array */
    protected $menus;


    /** @var string Clé du cache */
    const CACHE_KEY = '_aropixel.cache.menus';


    /**
     * MenuProvider constructor.
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;

        $this->menus = null;
    }


    protected function loadMenus()
    {
        // permet de récupérer l'entité correcte définis en parametre du front
        $cacheDuration = $this->parameterBag->get('aropixel_menu.cache.duration');

        //
        if ($cacheDuration) {
            $this->loadAndCache($cacheDuration);
        }
        else {
            $this->load();
        }

    }


    protected function load()
    {
        //
        $menuEntity = $this->parameterBag->get('aropixel_menu.entity');

        //
        $this->menus = $this->em->getRepository($menuEntity)->findRootsWithPage();

        //
        $this->splitMenus();
    }



    protected function loadAndCache($cacheDuration)
    {
        $em = $this->em;
        $menuEntity = $this->parameterBag->get('aropixel_menu.entity');

        //
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

        //
        $this->splitMenus();

    }


    protected function hydratePage(Menu $menuItem)
    {
        //
        $menuItem->getPage() && $menuItem->getPage()->getSlug();

        foreach ($menuItem->getChildren() as $child) {
            $this->hydratePage($child);
        }
    }


    protected function splitMenus()
    {
        $splittedMenus = array();

        /** @var Menu $menuItem */
        foreach ($this->menus as $menuItem) {
            $splittedMenus[$menuItem->getType()][] = $menuItem;
        }

        $this->menus = $splittedMenus;
    }


    public function getMenu($type)
    {
        if (is_null($this->menus)) {
            $this->loadMenus();
        }

        return array_key_exists($type, $this->menus) ? $this->menus[$type] : [];
    }


    public function refreshCache()
    {
        $cache = new FilesystemAdapter();
        $cache->delete(self::CACHE_KEY);

        $this->loadMenus();
    }

}

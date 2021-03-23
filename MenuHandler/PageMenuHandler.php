<?php

namespace Aropixel\MenuBundle\MenuHandler;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Model\MenuInputRessource;
use Aropixel\MenuBundle\Model\MenuInputRessources;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PageMenuHandler implements ItemMenuHandlerInterface
{

    private $_requiredPages;

    private $_pagesPublished;

    private $_staticPages;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    /**
     * @param $menuItems
     * @return MenuInputRessources|null
     *
     * get the static and dynamics pages with a class model in order to be displayed into the menu form
     */
    public function getInputRessources(array $menuItems): ?MenuInputRessources
    {

        $alreadyIncludedPages = $this->getAlreadyIncludedPages($menuItems);

        $menuInputPageRessources = new MenuInputRessources();

        $menuInputPageRessources->setResourceNameSingular('page');
        $menuInputPageRessources->setRessourceNamePlural('pages');
        $menuInputPageRessources->setRessourceLabel('Page Générale');
        $menuInputPageRessources->setRessourceColor('bg-pink');

        // create an input item for all the created pages
        foreach ($this->getPagesPublished() as $page) {
            if ($page->getType() == Page::TYPE_DEFAULT) {

                $menuInputPageRessource = new MenuInputRessource();

                $menuInputPageRessource->setLabel($page->getTitle());
                $menuInputPageRessource->setValue($page->getId());
                $menuInputPageRessource->setType('page');
                $menuInputPageRessource->setAlreadyIncluded(false);

                if (array_key_exists($page->getId(), $this->getStaticPages())) {
                    $menuInputPageRessource->setType('static');
                }

                if (in_array($page->getId(), $alreadyIncludedPages)) {
                    $menuInputPageRessource->setAlreadyIncluded(true);
                }

                 $menuInputPageRessources->addRessource($menuInputPageRessource);
            }
        }

        // create an input item for all the static pages
        foreach ($this->getStaticPages() as $key => $title) {

            $menuInputPageRessource = new MenuInputRessource();

            $menuInputPageRessource->setLabel($title);
            $menuInputPageRessource->setValue($key);
            $menuInputPageRessource->setType('page');
            $menuInputPageRessource->setAlreadyIncluded(false);

            if (array_key_exists($key, $this->getStaticPages())) {
                $menuInputPageRessource->setType('static');
            }

            if (in_array($key, $alreadyIncludedPages)) {
                $menuInputPageRessource->setAlreadyIncluded(true);
            }

             $menuInputPageRessources->addRessource($menuInputPageRessource);
        }

        return $menuInputPageRessources;
    }

    /**
     * @param array $menuItems
     * @param $type
     * @return array
     *
     * get the current menu page items
     */
    public function getMenuItems(array $menuItems, $type): array
    {

        // get all the mandatory pages in the menu config
        $requiredPages = $this->getRequiredPages($type);

        $entity = $this->params->get('aropixel_menu.entity');

        if ($this->isPageBundleActive()) {

            // for all the mandatory page
            foreach ($requiredPages as $code => $libelle) {

                $found = false;

                // for all the menu items already persisted
                /** @var Menu $item */
                foreach ($menuItems as $item) {

                    // if it's a static page and if the page is mandatory
                    $found = $this->itemContainsPage($item, $code);
                    if ($found) {
                        break;
                    }

                }

                // if the mandatory page is not saved into the menu
                if (!$found) {
                    // we create a new menu item for the page
                    $item = new $entity();
                    $item->setStaticPage($code);
                    $item->setTitle($libelle);
                    $item->setOriginalTitle($libelle);
                    $item->setType($type);
                    $item->setIsRequired(true);
                    $this->entityManager->persist($item);

                    $menuItems[] = $item;
                }
            }

            $this->entityManager->flush();

        }

        return $menuItems;
    }


    private function itemContainsPage(Menu $menuItem, $code)
    {
        if ($menuItem->getStaticPage() == $code) {
            return true;
        }

        $contains = false;
        if ($menuItem->getChildren()) {
            foreach ($menuItem->getChildren() as $child) {
                $contains |= $this->itemContainsPage($child, $code);
            }
        }

        return $contains;
    }

    /**
     * @param $item
     * @param $line
     *
     * add item page info before persisting it
     */
    public function hydrateMenuItem($item, $line): void
    {
        $static = null;
        $page = null;
        $title = null;

        $staticPages = $this->params->get('aropixel_menu.static_pages');

        // si c'est une page qui a été sauvée, on enrichit les variables $pages ou $static
        // pour les relier à la line
        if (strlen($item['data']['page'])) {
            if (array_key_exists($item['data']['page'], $staticPages)) {
                $static = $item['data']['page'];
            }
            else {
                $page = $this->entityManager->getRepository(Page::class)->find($item['data']['page']);
                $title = $page->getTitle();
            }
        }

        $line->setPage($page);
        $line->setTitle($title);

        //
        if (strlen($item['data']['static'])) {
            $static = $item['data']['static'];
        }

        $line->setStaticPage($static);
    }

    /**
     * @param array $linesItems
     *
     * nothing to do here after save
     */
    public function afterSave($type, array $linesItems): void
    {

    }

    public function getStaticPages()
    {
        if ((empty($this->_staticPages))) {
            $this->_staticPages = $this->params->get('aropixel_menu.static_pages');
        }
        return $this->_staticPages;
    }


    /**
     * @param array $menuItems
     * @return array
     *
     */
    public function getAlreadyIncludedPages(array $menuItems): array
    {
        $pagesAlreadyIncluded = [];

        /** @var Menu $item */
        foreach ($menuItems as $item) {
            if ($this->isPageBundleActive() && $item->getPage()) {

                $pagesAlreadyIncluded[] = $item->getPage()->getId();
            }

            if ($item->getStaticPage()) {
                $pagesAlreadyIncluded[] = $item->getStaticPage();
            }
        }

        return $pagesAlreadyIncluded;
    }

    /**
     * @return bool
     */
    public function isPageBundleActive(): bool
    {
        $bundles = $this->params->get('kernel.bundles');

        return array_key_exists('AropixelPageBundle', $bundles);
    }

    /**
     * @return mixed
     */
    private function getPagesPublished()
    {
        if ((empty($this->_pagesPublished))) {
            $this->_pagesPublished = $this->entityManager->getRepository(Page::class)->findPublished();
        }
        return $this->_pagesPublished;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getRequiredPages($type)
    {
        $menus = $this->params->get('aropixel_menu.menus');

//        if ((empty($this->_requiredPages))) {
//            $this->_requiredPages = $menus[$type]['required_pages'];
//        }
        return $menus[$type]['required_pages'];
    }

}

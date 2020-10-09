<?php

namespace Aropixel\MenuBundle\MenuHandler;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
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
     * @return array
     */
    public function getInputRessources($menuItems): array
    {

        $alreadyIncludedPages = $this->getAlreadyIncludedPages($menuItems);

        $inputPageRessources = [
            'resourceNameSingular' => 'page',
            'ressourceNamePlural' => 'pages',
            'ressourceLabel' => 'Page Générale',
            'ressourceColor' => 'bg-pink'
        ];

        $ressources = [];

        //Pour toutes les pages récupérées, on les ajoute dans l'array allPages
        foreach ($this->getPagesPublished() as $page) {
            if ($page->getType() == Page::TYPE_DEFAULT) {

                $ressource = [];

                $ressource['label'] = $page->getTitle();
                $ressource['value'] = $page->getId();
                $ressource['type'] = 'page';
                $ressource['alreadyIncluded'] = false;

                if (array_key_exists($page->getId(), $this->getStaticPages())) {
                    $ressource['type'] = 'static';
                }

                if (in_array($page->getId(), $alreadyIncludedPages)) {
                    $ressource['alreadyIncluded'] = true;
                }

                $ressources[$page->getId()] = $ressource;
            }
        }
        // pour toutes les statiques pages récupérées, on les ajoute dans l'array allPages
        foreach ($this->getStaticPages() as $key => $title) {

            $ressource = [];

            $ressource['label'] = $title;
            $ressource['value'] = $key;
            $ressource['type'] = 'page';
            $ressource['alreadyIncluded'] = false;

            if (array_key_exists($key, $this->getStaticPages())) {
                $ressource['type'] = 'static';
            }

            if (in_array($key, $alreadyIncludedPages)) {
                $ressource['alreadyIncluded'] = true;
            }

            $ressources[$key] = $ressource;
        }

        asort($ressources);

        $inputPageRessources['ressources'] = $ressources;

        return $inputPageRessources;
    }

    public function addToMenu(array $menuItems, $type): array
    {

        // récupère les pages obligatoires en config
        $requiredPages = $this->getRequiredPages($type);

        $entity = $this->params->get('aropixel_menu.entity');

        if ($this->isPageBundleActive()) {

            // pour toutes les pages obligatoire en config
            foreach ($requiredPages as $code => $libelle) {

                $found = false;


                // Pour chaque item de menu déjà sauvegardé
                /** @var Menu $item */
                foreach ($menuItems as $item) {

                    // si c'est une page statique et qu'elle notée en config
                    if ($item->getStaticPage() && $item->getStaticPage() == $code) {
                        // on la passe en obligatoire
                        $item->setIsRequired(true);
                        // et on met un flag pour dire que la page a déjà été traitée
                        $found = true;
                        break;
                    }
                }

                // si la page obliatoire n'a pas été trouvé dans les page déjà enregistrées
                if (!$found) {
                    // on créé un nouvel item de menu
                    $item = new $entity();
                    $item->setStaticPage($code);
                    $item->setTitle($libelle);
                    $item->setOriginalTitle($libelle);
                    $item->setType($type);
                    $item->setIsRequired(true);
                    $this->entityManager->persist($item);

                    // et on ajoute l'item de menu à l'array $menuPageItems
                    $menuItems[] = $item;
                }
            }

            $this->entityManager->flush();

        }



        return $menuItems;
    }

    public function hydrateMenuItem($item, $line): void
    {
        $static = null;
        $page = null;
        $title = null;

        $staticPages = $this->params->get('aropixel_menu.static_pages');

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

    private function getStaticPages()
    {
        if ((empty($this->_staticPages))) {
            $this->_staticPages = $this->params->get('aropixel_menu.static_pages');
        }
        return $this->_staticPages;
    }


    /**
     * @param array $menuItems
     * @return array
     */
    private function getAlreadyIncludedPages(array $menuItems): array
    {
        $pagesAlreadyIncluded = [];

        /** @var Menu $item */
        foreach ($menuItems as $item) {
            if ($this->isPageBundleActive() && $item->getPage()) {

                $pagesAlreadyIncluded[] = $item->getPage();
            }

            if ($item->getStaticPage()) {
                // on ajoute son code dans l'array $alreadyIncluded
                $pagesAlreadyIncluded[] = $item->getStaticPage();
            }
        }

        return $pagesAlreadyIncluded;
    }

    /**
     * @return bool
     */
    private function isPageBundleActive(): bool
    {
        // vérifie si le page bundle est activé ou pas
        $bundles = $this->params->get('kernel.bundles');

        $isPageBundleActive = array_key_exists('AropixelPageBundle', $bundles);
        return $isPageBundleActive;
    }

    private function getPagesPublished()
    {
        if ((empty($this->_pagesPublished))) {
            $this->_pagesPublished = $this->entityManager->getRepository(Page::class)->findPublished();
        }
        return $this->_pagesPublished;
    }

    private function getRequiredPages($type)
    {
        $menus = $this->params->get('aropixel_menu.menus');

        if ((empty($this->_requiredPages))) {
            $this->_requiredPages = $menus[$type]['required_pages'];
        }
        return $this->_requiredPages;
    }

}

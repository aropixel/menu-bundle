<?php

namespace Aropixel\MenuBundle\MenuAdder;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PagesMenuAdder
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
    public function getAllPages(): array
    {
        $allPages = [];

        //Pour toutes les pages récupérées, on les ajoute dans l'array allPages
        foreach ($this->getPagesPublished() as $page) {
            if ($page->getType() == Page::TYPE_DEFAULT) {
                $allPages[$page->getId()] = $page->getTitle();
            }
        }
        // pour toutes les statiques pages récupérées, on les ajoute dans l'array allPages
        foreach ($this->getStaticPages() as $key => $title) {
            $allPages[$key] = $title;
        }

        asort($allPages);

        return $allPages;
    }


    public function getMenuPages($type, $menuItems): array
    {
        // récupère les pages obligatoires en config
        $requiredPages = $this->getRequiredPages($type);

        $entity = $this->params->get('aropixel_menu.entity');

        $pages = [];
        if ($this->isPageBundleActive()) {

            // récupère toutes les pages publiées
            $pages = $this->getPagesPublished();

            //
            $menuPageItems = [];

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
                    $menuPageItems[] = $item;
                }
            }

        }
        return $menuPageItems;
    }

    /**
     * @return bool
     */
    public function isPageBundleActive(): bool
    {
        // vérifie si le page bundle est activé ou pas
        $bundles = $this->params->get('kernel.bundles');

        $isPageBundleActive = array_key_exists('AropixelPageBundle', $bundles);
        return $isPageBundleActive;
    }

    public function getPagesPublished()
    {
        if ((empty($this->_pagesPublished))) {
            $this->_pagesPublished = $this->entityManager->getRepository(Page::class)->findPublished();
        }
        return $this->_pagesPublished;
    }

    public function getStaticPages()
    {
        if ((empty($this->_staticPages))) {
            $this->_staticPages = $this->params->get('aropixel_menu.static_pages');
        }
        return $this->_staticPages;
    }

    public function getRequiredPages($type)
    {
        $menus = $this->params->get('aropixel_menu.menus');

        if ((empty($this->_requiredPages))) {
            $this->_requiredPages = $menus[$type]['required_pages'];
        }
        return $this->_requiredPages;
    }

}

<?php


namespace Aropixel\MenuBundle\MenuAdder;


use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MenuHandler
{
    /**
    * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var PagesMenuHandler
     */
    private $pagesMenuHandler;

    /**
     * @var LinkMenuHandler
     */
    private $linkMenuHandler;

    /**
     * @var CategoryMenuHandler
     */
    private $categoryMenuHandler;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        PagesMenuHandler $pagesMenuHandler,
        LinkMenuHandler $linkMenuHandler,
        CategoryMenuHandler $categoryMenuHandler
    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->pagesMenuHandler = $pagesMenuHandler;
        $this->linkMenuHandler = $linkMenuHandler;
        $this->categoryMenuHandler = $categoryMenuHandler;
    }


    // on ajoute les nouveaux items de menu aux items déjà enregistrés
    public function addToMenu($type)
    {
        $menuItems = $this->getMenuItems($type);

        $menuItems = $this->pagesMenuHandler->addToMenu($menuItems, $type);
        $menuItems = $this->linkMenuHandler->addToMenu($menuItems, $type);

        return $menuItems;
    }

    public function getInputRessources($menuItems)
    {
        $inputRessources = [];

        if (!empty($this->pagesMenuHandler->getInputRessources($menuItems))) {
            $inputRessources[] = $this->pagesMenuHandler->getInputRessources($menuItems);
        }

        if (!empty($this->linkMenuHandler->getInputRessources($menuItems))) {
            $inputRessources[] = $this->linkMenuHandler->getInputRessources($menuItems);
        }

        if (!empty($this->categoryMenuHandler->getInputRessources($menuItems))) {
            $inputRessources[] = $this->categoryMenuHandler->getInputRessources($menuItems);
        }

        return $inputRessources;
    }

    public function saveMenuItem($type, $item, Menu $parent=null)
    {
        $entity = $this->params->get('aropixel_menu.entity');

        /** @var Menu $line */
        $line = new $entity();
        $line->setType($type);

        //
        if (!is_null($parent)) {
            $line->setParent($parent);
        }

        //
        $title = "";

        $this->pagesMenuHandler->hydrateMenuItem($item, $line);
        $this->linkMenuHandler->hydrateMenuItem($item, $line);
        $this->categoryMenuHandler->hydrateMenuItem($item, $line);

        //
        if (strlen($item['data']['title'])) {
            $title = $item['data']['title'];
        }

        //
        if (strlen($item['data']['originalTitle'])) {
            $originalTitle = $item['data']['originalTitle'];
            $line->setOriginalTitle($originalTitle);
        }

        $line->setTitle($title);

        //
        if (isset($item['children'])) {
            foreach ($item['children'] as $i => $sbitem) {
                $this->saveMenuItem($type, $sbitem, $line);
            }
        }
        else {
            $this->entityManager->persist($line);
        }

        return $line;
    }

    // récupère tous les menus items déjà enregistrés
    private function getMenuItems($type)
    {
        $entity = $this->params->get('aropixel_menu.entity');

        $menuItems = $this->entityManager->getRepository($entity)->findBy(array(
            'parent' => null,
            'type' => $type
        ));

        return $menuItems;
    }

}

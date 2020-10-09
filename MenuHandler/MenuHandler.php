<?php


namespace Aropixel\MenuBundle\MenuHandler;


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

    private $menuHandlers;


    public function __construct(
        iterable $menuHandlers,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    )
    {
        $this->menuHandlers = iterator_to_array($menuHandlers);

        $this->entityManager = $entityManager;
        $this->params = $params;
    }


    /**
     * @param $type
     * @return object[]
     *
     * récupère les items sauvés en bdd en fonction de leur type (categorie etc) via
     * des différents services menuHandler (injectés  dans le constructor grâce au tag)
     *
     */
    public function addToMenu($type)
    {
        $menuItems = $this->getMenuItems($type);

        foreach ($this->menuHandlers as $menuHandler) {
            $menuItems = $menuHandler->addToMenu($menuItems, $type);
        }

        return $menuItems;
    }

    /**
     * @param $menuItems
     * @return array
     *
     * récupère les ressources des différents services menuHandler (injectés dans le constructor grâce au tag)
     * dans le but de les affcher en sélection pour créer le menu
     */
    public function getInputRessources($menuItems)
    {
        $inputRessources = [];

        foreach ($this->menuHandlers as $menuHandler) {
            if (!empty($menuHandler->getInputRessources($menuItems))) {
                $inputRessources[] = $menuHandler->getInputRessources($menuItems);
            }
        }

        return $inputRessources;
    }

    /**
     * @param $type
     * @param $item
     * @param Menu|null $parent
     * @return Menu
     *
     * persist un item de menu en fonction de son type (en faisant appel aux différents menu handler)
     */
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

        foreach ($this->menuHandlers as $menuHandler) {
            $menuHandler->hydrateMenuItem($item, $line);
        }

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

    /**
     * @param $type
     * @return object[]
     */
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

<?php


namespace Aropixel\MenuBundle\MenuAdder;


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

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        PagesMenuHandler $pagesMenuHandler
    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->pagesMenuHandler = $pagesMenuHandler;
    }


    // on ajoute les nouveaux items de menu aux items déjà enregistrés
    public function addToMenu($type)
    {
        $menuItems = $this->getMenuItems($type);
        $menuPageItems = $this->pagesMenuHandler->getMenuPages($type, $menuItems);
        $menuItems+= $menuPageItems;

        return $menuItems;
    }

    public function getInputRessources($menuItems)
    {
        $inputRessources = [];

        $inputRessources[] = $this->pagesMenuHandler->getInputRessources($menuItems);

        return $inputRessources;
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

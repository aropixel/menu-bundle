<?php


namespace Aropixel\MenuBundle\MenuAdder;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MenuAdder
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
     * @var PagesMenuAdder
     */
    private $pagesMenuAdder;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        PagesMenuAdder $pagesMenuAdder
    )
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->pagesMenuAdder = $pagesMenuAdder;
    }


    // on ajoute les nouveaux items de menu aux items déjà enregistrés
    public function addToMenu($type)
    {
        $menuItems = $this->getMenuItems($type);
        $menuPageItems = $this->pagesMenuAdder->getMenuPages($type, $menuItems);
        $menuItems+= $menuPageItems;

        return $menuItems;
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

<?php

namespace Aropixel\MenuBundle\Controller;

use Aropixel\MenuBundle\MenuHandler\MenuHandler;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class MenuController extends AbstractController
{

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly MenuHandler $menuHandler,
        protected readonly MenuProviderInterface $menuProvider,
    ) {
    }

    public function index($type): Response
    {
        // get the menus config
        $menus = $this->getParameter('aropixel_menu.menus');

        if (!array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        // get all menu items
        $menuItems = $this->menuHandler->getMenu($type);

        // get the values for the menu form (pages, link etc)
        $inputRessources = $this->menuHandler->getInputRessources($menuItems);

        return $this->render('@AropixelMenu/menu/menu.html.twig', [
            'menus' => $menus,
            'type_menu' => $type,
            'menu' => $menuItems,
            'inputRessources' => $inputRessources,
        ]);
    }


    public function save(Request $request)
    {
        $type = $request->request->get('type');

        $menus = $this->getParameter('aropixel_menu.menus');
        if (!array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        $entity = $this->getParameter('aropixel_menu.entity');
        $this->entityManager->getRepository($entity)->deleteMenu($type);
        $this->entityManager->flush();

        $menuItems = $request->request->all()['menu'];

        $this->menuHandler->saveMenu($type, $menuItems);
        $this->menuProvider->refreshCache();

        return new Response('OK', Response::HTTP_OK);
    }

}

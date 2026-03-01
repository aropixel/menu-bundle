<?php

namespace Aropixel\MenuBundle\Controller;

use Aropixel\MenuBundle\MenuHandler\MenuManager;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly MenuManager $menuManager,
        protected readonly MenuProviderInterface $menuProvider,
    ) {
    }

    public function index(string $type): Response
    {
        // get the menus config
        $menus = $this->getParameter('aropixel_menu.menus');

        if (!\array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        // get all menu items
        $menuItems = $this->menuManager->getMenu($type);

        // get the available sources for the menu form
        $availableSources = $this->menuManager->getAvailableSources($menuItems);

        return $this->render('@AropixelMenu/menu/menu.html.twig', [
            'menus' => $menus,
            'type_menu' => $type,
            'menu' => $menuItems,
            'availableSources' => $availableSources,
            'menuManager' => $this->menuManager,
        ]);
    }

    public function save(Request $request): Response
    {
        $type = $request->request->get('type');

        $menus = $this->getParameter('aropixel_menu.menus');
        if (!\array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        $entity = $this->getParameter('aropixel_menu.entity');
        $this->entityManager->getRepository($entity)->deleteMenu($type);
        $this->entityManager->flush();

        $menuItems = $request->request->all()['menu'];

        $this->menuManager->saveMenu($type, $menuItems);
        $this->menuProvider->refreshCache();

        return new Response('OK', Response::HTTP_OK);
    }
}

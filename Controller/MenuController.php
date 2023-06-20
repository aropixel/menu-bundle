<?php

namespace Aropixel\MenuBundle\Controller;

use Aropixel\MenuBundle\MenuHandler\MenuHandler;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("menu")
 */
class MenuController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{type}/edit", name="menu_index", methods="GET")
     */
    public function index(
        $type,
        MenuHandler $menuHandler
    ): Response
    {
        // get the menus config
        $menus = $this->getParameter('aropixel_menu.menus');

        if (!array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        // get all menu items
        $menuItems = $menuHandler->getMenu($type);

        // get the values for the menu form (pages, link etc)
        $inputRessources = $menuHandler->getInputRessources($menuItems);

        return $this->render('@AropixelMenu/menu/menu.html.twig', [
            'menus' => $menus,
            'type_menu' => $type,
            'menu' => $menuItems,
            'inputRessources' => $inputRessources,
        ]);
    }

    /**
     * @Route("/save", name="menu_save", methods="POST")
     */
    public function save(
        Request $request,
        MenuProviderInterface $menuProvider,
        MenuHandler $menuHandler
    )
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

        $menuHandler->saveMenu($type, $menuItems);
        $menuProvider->refreshCache();

        return new Response('OK', Response::HTTP_OK);
    }

}

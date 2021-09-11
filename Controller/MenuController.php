<?php

namespace Aropixel\MenuBundle\Controller;

use Aropixel\MenuBundle\MenuHandler\MenuHandler;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("menu")
 */
class MenuController extends AbstractController
{

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
        //
        $type = $request->request->get('type');

        //
        $menus = $this->getParameter('aropixel_menu.menus');
        if (!array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        //
        $entity = $this->getParameter('aropixel_menu.entity');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository($entity)->deleteMenu($type);
        $em->flush();

        //
        $singleRoot = null;
        $menuItems = $request->request->get('menu');

        $menuHandler->saveMenu($type, $menuItems);

        //
        $menuProvider->refreshCache();

        //
        return new Response('OK', Response::HTTP_OK);
    }

}

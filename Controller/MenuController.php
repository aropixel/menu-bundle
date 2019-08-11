<?php

namespace Aropixel\MenuBundle\Controller;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
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
    public function index($type): Response
    {

        //
        $menus = $this->getParameter('aropixel_menu.menus');
        if (!array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }


        //
        $em = $this->getDoctrine();
        $entity = $this->getParameter('aropixel_menu.entity');
        $menuItems = $em->getRepository($entity)->findBy(array(
            'parent' => null,
            'type' => $type
        ));




        $bundles = $this->getParameter('kernel.bundles');
        $staticPages = $this->getParameter('aropixel_menu.static_pages');
        $isPageBundleActive = array_key_exists('AropixelPageBundle', $bundles);

        $pages = array();
        if ($isPageBundleActive) {
            $pages = $this->getDoctrine()->getRepository(Page::class)->findPublished();
        }


        //
        $alreadyIncluded = array(
            'pages' => array(),
        );


        /** @var Menu $item */
        foreach ($menuItems as $item) {


            /** @var Page $page */
            if ($isPageBundleActive && $page = $item->getPage()) {
                $alreadyIncluded['pages'][] = $page->getId();
            }


            //
            if ($code = $item->getStaticPage()) {
                $alreadyIncluded['pages'][] = $code;
            }


            //
            if ($link = $item->getLink()) {
                $parsing = parse_url($link);
                if ($parsing){
                    $item->setLinkDomain($parsing['host']);
                }
                else {
                    $item->setLinkDomain($link);
                }
            }

        }

        $allPages = array();
        foreach ($pages as $page) {
            $code = $page->getCode();
            if (is_null($code) || !strlen($code)) {
                $allPages[$page->getId()] = $page->getTitle();
            }
        }
        foreach ($staticPages as $key => $title) {
            $allPages[$key] = $title;
        }

        asort($allPages);


        //
        return $this->render('@AropixelMenu/menu/menu.html.twig', [
            'menus' => $menus,
            'type_menu' => $type,
            'menu' => $menuItems,
            'available_pages' => $allPages,
            'already_included' => $alreadyIncluded,
        ]);
    }




    /**
     * @Route("/save", name="menu_save", methods="POST")
     */
    public function save(Request $request)
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

        //
        foreach ($menuItems as $i => $item) {
            $this->saveMenuItem($type, $item, $singleRoot);
        }

        //
        $em->flush();

        //
        return new Response('OK', Response::HTTP_OK);
    }



    private function saveMenuItem($type, $item, Menu $parent=null)
    {
        //
        $entity = $this->getParameter('aropixel_menu.entity');
        $em = $this->getDoctrine()->getManager();

        /** @var Menu $ligne */
        $ligne = new $entity();
        $ligne->setType($type);

        //
        if (!is_null($parent)) {
            $ligne->setParent($parent);
        }

        //
        $title = "";
        $static = $page = $link = null;
        $staticPages = $this->getParameter('aropixel_menu.static_pages');

        if (strlen($item['data']['page'])) {
            if (array_key_exists($item['data']['page'], $staticPages)) {
                $static = $item['data']['page'];
            }
            else {
                $page = $em->getRepository(Page::class)->find($item['data']['page']);
                $title = $page->getTitle();
            }
        }

        //
        if (strlen($item['data']['static'])) {
            $static = $item['data']['static'];
        }

        //
        if (strlen($item['data']['title'])) {
            $title = $item['data']['title'];
        }

        //
        if (strlen($item['data']['originalTitle'])) {
            $originalTitle = $item['data']['originalTitle'];
            $ligne->setOriginalTitle($originalTitle);
        }

        //
        if (strlen($item['data']['link'])) {
            $link = $item['data']['link'];
        }

        $ligne->setPage($page);
        $ligne->setStaticPage($static);
        $ligne->setTitle($title);
        $ligne->setLink($link);

        //
        if (isset($item['children'])) {
            foreach ($item['children'] as $i => $sbitem) {
                $this->saveMenuItem($type, $sbitem, $ligne);
            }
        }
        else {
            $em->persist($ligne);
        }

        return $ligne;
    }


}

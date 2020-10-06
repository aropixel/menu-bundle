<?php

namespace Aropixel\MenuBundle\Controller;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\MenuAdder\MenuAdder;
use Aropixel\MenuBundle\MenuAdder\PagesMenuAdder;
use Aropixel\MenuBundle\Provider\MenuProvider;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
        EntityManagerInterface $entityManager,
        PagesMenuAdder $pagesMenuAdder,
        MenuAdder $menuAdder
    ): Response
    {
        // récupère la config des différents menus (footer, navbar etc)
        $menus = $this->getParameter('aropixel_menu.menus');

        if (!array_key_exists($type, $menus)) {
            throw $this->createNotFoundException();
        }

        $menuItems = $menuAdder->addToMenu($type);

        // récupère toutes les pages (statiques + celle du bundle page bundle)
        // et les trie dans un array
        $allPages = $pagesMenuAdder->getAllPages();

        $entityManager->flush();

        //
        $alreadyIncluded = array(
            'pages' => array(),
        );


        // pour chaque item de menu on vérifie globalement si l'item a déjà été ajouté
        // au menu, pour ensuite le bloquer au re-ajout dans le menu
        /** @var Menu $item */
        foreach ($menuItems as $item) {


            /** @var Page $page */
            // si le menu item est une page
            if ($pagesMenuAdder->isPageBundleActive() && $page = $item->getPage()) {
                // on ajoute son id dans l'array $alreadyIncluded
                $alreadyIncluded['pages'][] = $page->getId();
            }


            // si l'item est une page statique
            if ($code = $item->getStaticPage()) {
                // on ajoute son code dans l'array $alreadyIncluded
                $alreadyIncluded['pages'][] = $code;
            }


            // si l'item est un lien
            if ($link = $item->getLink()) {
                // on clean le lien et on le modifie pour l'item
                $parsing = parse_url($link);
                if ($parsing){
                    $item->setLinkDomain($parsing['host']);
                }
                else {
                    $item->setLinkDomain($link);
                }
            }

        }

        $staticPages = $pagesMenuAdder->getStaticPages();
        $requiredPages = $pagesMenuAdder->getRequiredPages($type);

        //
        return $this->render('@AropixelMenu/menu/menu.html.twig', [
            'menus' => $menus,
            'type_menu' => $type,
            'required_pages' => $requiredPages,
            'menu' => $menuItems,
            'static_pages' => array_keys($staticPages),
            'available_pages' => $allPages,
            'already_included' => $alreadyIncluded,
        ]);
    }

    /**
     * @Route("/save", name="menu_save", methods="POST")
     */
    public function save(Request $request, MenuProviderInterface $menuProvider)
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
        $menuProvider->refreshCache();

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
        if ($item['data']['type'] == 'link' && !strlen($item['data']['link'])) {
            $item['data']['link'] = '#';
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

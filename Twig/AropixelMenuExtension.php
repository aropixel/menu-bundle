<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/05/2019 à 13:32
 */

namespace Aropixel\MenuBundle\Twig;


use Aropixel\MenuBundle\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


abstract class AropixelMenuExtension extends AbstractExtension
{

    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var UrlGeneratorInterface */
    protected $router;


    /**
     * AropixelMenuExtension constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $router
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, UrlGeneratorInterface $router)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
        $this->router = $router;
    }


    public function getFilters()
    {
        return array(
            new TwigFilter('get_link', array($this, 'getLink')),
        );
    }


    public function getFunctions()
    {
        return array(
            new TwigFunction('get_menu', array($this, 'getMenu')),
        );
    }



    public function getMenu($type)
    {
        $menuItems = $this->em->getRepository(Menu::class)->findBy(array(
            'parent' => null,
            'type' => $type
        ));

        return $menuItems;
    }


    abstract public function getLink(Menu $menuItem) : string;



}

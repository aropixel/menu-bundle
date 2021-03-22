<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/05/2019 à 13:32
 */

namespace Aropixel\MenuBundle\Twig;


use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Provider\MenuProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


abstract class AropixelMenuExtension extends AbstractExtension
{

    /** @var RequestStack */
    protected $request;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var RequestStack */
    private $requestStack;

    /** @var MenuProvider */
    private $menuProvider;


    /**
     * AropixelMenuExtension constructor.
     * @param RequestStack $requestStack
     * @param UrlGeneratorInterface $router
     * @param MenuProvider $menuProvider
     */
    public function __construct(
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        MenuProvider $menuProvider
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->menuProvider = $menuProvider;
    }


    public function getFilters()
    {
        return array(
            new TwigFilter('get_link', array($this, 'getLink')),
            new TwigFilter('is_section', array($this, 'isSection')),
        );
    }


    public function getFunctions()
    {
        return array(
            new TwigFunction('get_menu', array($this, 'getMenu')),
        );
    }



    public function isSection(Menu $menu)
    {
        return !$menu->getPage() && !$menu->getStaticPage() && !$menu->getLink();
    }


    public function getMenu($type)
    {
        return $this->menuProvider->getMenu($type);
    }


    abstract public function getLink(Menu $menuItem) : string;



}

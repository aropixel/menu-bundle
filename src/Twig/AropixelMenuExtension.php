<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/05/2019 à 13:32
 */

namespace Aropixel\MenuBundle\Twig;


use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


abstract class AropixelMenuExtension extends AbstractExtension
{

    protected ?Request $request = null;
    private readonly RequestStack $requestStack;

    public function __construct(
        RequestStack $requestStack,
        protected UrlGeneratorInterface $router,
        private readonly MenuProviderInterface $menuProvider,
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->requestStack = $requestStack;
    }


    public function getFilters()
    {
        return [new TwigFilter('get_link', $this->getLink(...)), new TwigFilter('is_section', $this->isSection(...))];
    }


    public function getFunctions()
    {
        return [new TwigFunction('get_menu', $this->getMenu(...))];
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

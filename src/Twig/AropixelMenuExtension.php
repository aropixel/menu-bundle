<?php

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

    public function __construct(
        private readonly RequestStack $requestStack,
        protected readonly UrlGeneratorInterface $router,
        private readonly MenuProviderInterface $menuProvider,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFilters(): array
    {
        return [new TwigFilter('get_link', $this->getLink(...)), new TwigFilter('is_section', $this->isSection(...))];
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('get_menu', $this->getMenu(...))];
    }

    public function isSection(Menu $menu): bool
    {
        return !$menu->getPage() && !$menu->getStaticPage() && !$menu->getLink();
    }

    public function getMenu(string $type): array
    {
        return $this->menuProvider->getMenu($type);
    }

    abstract public function getLink(Menu $menuItem): string;
}

<?php

namespace Aropixel\MenuBundle\Twig;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Aropixel\MenuBundle\Provider\MenuProviderInterface;
use Aropixel\MenuBundle\Source\MenuSourceChain;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AropixelMenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly MenuProviderInterface $menuProvider,
        private readonly MenuSourceChain $sourceChain,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('get_link', $this->getLink(...)),
            new TwigFilter('is_section', $this->isSection(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('get_menu', $this->getMenu(...))];
    }

    public function getLink(MenuInterface $menuItem): string
    {
        return $this->sourceChain->resolveUrl($menuItem);
    }

    public function isSection(MenuInterface $menuItem): bool
    {
        return $this->sourceChain->isSection($menuItem);
    }

    public function getMenu(string $type): array
    {
        return $this->menuProvider->getMenu($type);
    }
}

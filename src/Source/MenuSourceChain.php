<?php

namespace Aropixel\MenuBundle\Source;

use Aropixel\MenuBundle\Entity\MenuInterface;

class MenuSourceChain
{
    /** @param iterable<MenuSourceInterface> $sources */
    public function __construct(
        private readonly iterable $sources,
    ) {
    }

    public function resolveUrl(MenuInterface $menuItem): string
    {
        $type = $menuItem->getType();

        if (null === $type) {
            return '#';
        }

        foreach ($this->sources as $source) {
            if ($source->supports($type)) {
                return $source->resolveUrl($menuItem);
            }
        }

        return '#';
    }

    public function isSection(MenuInterface $menuItem): bool
    {
        $type = $menuItem->getType();

        if (null === $type) {
            return true;
        }

        foreach ($this->sources as $source) {
            if ($source->supports($type)) {
                return 'section' === $source->getName();
            }
        }

        return false;
    }
}

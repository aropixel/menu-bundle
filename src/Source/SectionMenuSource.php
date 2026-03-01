<?php

namespace Aropixel\MenuBundle\Source;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SectionMenuSource implements MenuSourceInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getName(): string
    {
        return 'section';
    }

    public function getLabel(): string
    {
        return $this->translator->trans('aropixel.menu.form.type.section');
    }

    public function getColor(): string
    {
        return 'bg-dark-grey';
    }

    public function getAvailableItems(array $menuItems): array
    {
        return [];
    }

    public function getSelectionTemplate(): string
    {
        return '@AropixelMenu/menu/sources/section.html.twig';
    }

    public function supports(string $type): bool
    {
        return 'section' === $type;
    }

    public function getPayload(MenuInterface $menuItem): array
    {
        return [];
    }

    public function mapToEntity(array $data, MenuInterface $menuItem): void
    {
        $menuItem->setLink(null);
        $menuItem->setPage(null);
        $menuItem->setStaticPage(null);
    }
}

<?php

namespace Aropixel\MenuBundle\Source;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LinkMenuSource implements MenuSourceInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getName(): string
    {
        return 'link';
    }

    public function getLabel(): string
    {
        return $this->translator->trans('aropixel.menu.form.type.link');
    }

    public function getColor(): string
    {
        return 'bg-teal';
    }

    public function getAvailableItems(array $menuItems): array
    {
        return [];
    }

    public function getSelectionTemplate(): string
    {
        return '@AropixelMenu/menu/sources/link.html.twig';
    }

    public function supports(string $type): bool
    {
        return 'link' === $type;
    }

    public function getPayload(MenuInterface $menuItem): array
    {
        return [
            'link' => $menuItem->getLink(),
            'linkDomain' => $menuItem->getLinkDomain(),
        ];
    }

    public function resolveUrl(MenuInterface $menuItem): string
    {
        return $menuItem->getLink() ?? '#';
    }

    public function mapToEntity(array $data, MenuInterface $menuItem): void
    {
        $link = $data['link'] ?? null;

        if (null === $link || 0 === mb_strlen((string) $link)) {
            $link = '#';
        }

        $menuItem->setLink($link);

        // Optional: we could also populate linkDomain here if necessary
        // or let the MenuManager handle it during loading.
        $parsing = parse_url($link);
        if (\is_array($parsing) && \array_key_exists('host', $parsing)) {
            $menuItem->setLinkDomain($parsing['host']);
        } else {
            $menuItem->setLinkDomain($link);
        }
    }
}

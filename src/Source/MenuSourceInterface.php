<?php

namespace Aropixel\MenuBundle\Source;

use Aropixel\MenuBundle\Entity\MenuInterface;

interface MenuSourceInterface
{
    /**
     * Returns the technical name of the source (e.g., "page", "link", "section").
     */
    public function getName(): string;

    /**
     * Returns the label displayed in the interface (e.g., "Pages", "Manual link").
     */
    public function getLabel(): string;

    /**
     * Returns the color of the badge (e.g., "bg-pink", "bg-teal").
     */
    public function getColor(): string;

    /**
     * Returns the selectable items for this source.
     */
    public function getAvailableItems(array $menuItems): array;

    /**
     * Returns the Twig template path to render the selection form for this source.
     */
    public function getSelectionTemplate(): string;

    /**
     * Determines if this source supports the given item type.
     */
    public function supports(string $type): bool;

    /**
     * Extracts specific data from the menu item for the JSON payload.
     */
    public function getPayload(MenuInterface $menuItem): array;

    /**
     * Hydrates the Menu entity from the received data (payload).
     */
    public function mapToEntity(array $data, MenuInterface $menuItem): void;

    /**
     * Resolves the menu item to a URL for front-end rendering.
     */
    public function resolveUrl(MenuInterface $menuItem): string;
}

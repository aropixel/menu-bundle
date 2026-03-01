<?php

namespace Aropixel\MenuBundle\MenuHandler;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Entity\MenuInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MenuManager
{
    public function __construct(
        #[AutowireIterator('aropixel_menu.source')]
        private readonly iterable $sources,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $params
    ) {
    }

    public function getMenu(string $type): array
    {
        $entityClass = $this->params->get('aropixel_menu.entity');
        $repository = $this->entityManager->getRepository($entityClass);

        $rootItems = $repository->findBy(['parent' => null, 'type' => $type]);

        foreach ($rootItems as $item) {
            $this->hydrateItem($item);
        }

        return $rootItems;
    }

    private function hydrateItem(MenuInterface $item): void
    {
        // Recursively hydrate children if needed
        foreach ($item->getChildren() as $child) {
            $this->hydrateItem($child);
        }
    }

    public function getSources(): iterable
    {
        return $this->sources;
    }

    public function getAvailableSources(array $menuItems): array
    {
        $availableSources = [];
        foreach ($this->sources as $source) {
            $items = $source->getAvailableItems($menuItems);
            if (!empty($items) || \in_array($source->getName(), ['link', 'section'])) {
                $availableSources[] = [
                    'source' => $source,
                    'items' => $items,
                ];
            }
        }

        return $availableSources;
    }

    public function saveMenu(string $type, array $menuData): void
    {
        $entityClass = $this->params->get('aropixel_menu.entity');

        // Delete old menu items for this type
        $this->entityManager->getRepository($entityClass)->deleteMenu($type);
        $this->entityManager->flush();

        foreach ($menuData as $data) {
            $this->saveMenuItem($type, $data);
        }

        $this->entityManager->flush();
    }

    private function saveMenuItem(string $type, array $data, ?MenuInterface $parent = null): MenuInterface
    {
        $entityClass = $this->params->get('aropixel_menu.entity');
        /** @var MenuInterface $menuItem */
        $menuItem = new $entityClass();
        $menuItem->setType($type);
        $menuItem->setParent($parent);

        $itemData = $data['data'] ?? [];
        $payload = $itemData['payload'] ?? [];
        $itemType = $itemData['type'] ?? '';

        // Hydrate common fields
        $menuItem->setTitle($itemData['title'] ?? '');
        $menuItem->setOriginalTitle($itemData['originalTitle'] ?? '');

        // Find the source that supports this item type and hydrate the entity
        foreach ($this->sources as $source) {
            if ($source->supports($itemType)) {
                $source->mapToEntity($payload, $menuItem);

                break;
            }
        }

        $this->entityManager->persist($menuItem);

        if (isset($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->saveMenuItem($type, $childData, $menuItem);
            }
        }

        return $menuItem;
    }

    public function getPayload(MenuInterface $menuItem): array
    {
        // Try to find a source that supports the menu item to get its payload
        foreach ($this->sources as $source) {

            // Check if the source supports the menu item
            // For example, LinkMenuSource supports if it's a link, PageMenuSource if it's a page, etc.
            if ($source->supports('link') && $menuItem->getLink()) {
                return $source->getPayload($menuItem);
            }

            if ($source->supports('page') && ($menuItem->getPage() || $menuItem->getStaticPage())) {
                return $source->getPayload($menuItem);
            }

            // Fallback for general support if the source can determine it from the entity
            if ($source->supports($menuItem->getType() ?: '')) {
                return $source->getPayload($menuItem);
            }
        }

        // Fallback for types stored directly in the entity if no source matches
        if ($menuItem->getLink()) {
            return ['link' => $menuItem->getLink()];
        }
        if ($menuItem->getStaticPage()) {
            return ['type' => 'static', 'value' => $menuItem->getStaticPage()];
        }
        if ($menuItem->getPage()) {
            return ['type' => 'page', 'value' => $menuItem->getPage()->getId()];
        }

        return [];
    }
}

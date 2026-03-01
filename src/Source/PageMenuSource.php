<?php

namespace Aropixel\MenuBundle\Source;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageMenuSource implements MenuSourceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $params,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getName(): string
    {
        return 'page';
    }

    public function getLabel(): string
    {
        return $this->translator->trans('aropixel.menu.form.type.page');
    }

    public function getColor(): string
    {
        return 'bg-pink';
    }

    public function getAvailableItems(array $menuItems): array
    {
        $items = [];
        $alreadyIncluded = $this->getAlreadyIncluded($menuItems);

        // Published pages
        if ($this->isPageBundleActive()) {
            $pages = $this->entityManager->getRepository(Page::class)->findAll();
            foreach ($pages as $page) {
                $items[] = [
                    'label' => $page->getTitle(),
                    'value' => $page->getId(),
                    'type' => 'page',
                    'alreadyIncluded' => \in_array($page->getId(), $alreadyIncluded),
                ];
            }
        }

        // Configured static pages
        $staticPages = $this->params->get('aropixel_menu.static_pages');
        foreach ($staticPages as $key => $title) {
            $items[] = [
                'label' => $this->translator->trans($title),
                'value' => $key,
                'type' => 'static',
                'alreadyIncluded' => \in_array($key, $alreadyIncluded),
            ];
        }

        return $items;
    }

    public function getSelectionTemplate(): string
    {
        return '@AropixelMenu/menu/sources/page.html.twig';
    }

    public function supports(string $type): bool
    {
        return 'page' === $type || 'static' === $type;
    }

    public function getPayload(MenuInterface $menuItem): array
    {
        if ($menuItem->getStaticPage()) {
            return [
                'type' => 'static',
                'value' => $menuItem->getStaticPage(),
            ];
        }

        if ($menuItem->getPage()) {
            return [
                'type' => 'page',
                'value' => $menuItem->getPage()->getId(),
            ];
        }

        return [];
    }

    public function mapToEntity(array $data, MenuInterface $menuItem): void
    {
        $type = $data['type'] ?? null;
        $value = $data['value'] ?? null;

        if ('static' === $type) {
            $menuItem->setStaticPage($value);
            $menuItem->setPage(null);

            // Try to find the title in the config
            $staticPages = $this->params->get('aropixel_menu.static_pages');
            if (isset($staticPages[$value])) {
                $menuItem->setTitle($this->translator->trans($staticPages[$value]));
            }
        } elseif ('page' === $type) {
            $page = $this->entityManager->getRepository(Page::class)->find($value);
            $menuItem->setPage($page);
            $menuItem->setStaticPage(null);
            if ($page) {
                $menuItem->setTitle($page->getTitle());
            }
        }
    }

    private function getAlreadyIncluded(array $menuItems): array
    {
        $included = [];
        foreach ($menuItems as $item) {
            if ($item->getStaticPage()) {
                $included[] = $item->getStaticPage();
            }
            if ($item->getPage()) {
                $included[] = $item->getPage()->getId();
            }
            if ($item->getChildren()) {
                $included = array_merge($included, $this->getAlreadyIncluded($item->getChildren()->toArray()));
            }
        }

        return array_unique($included);
    }

    private function isPageBundleActive(): bool
    {
        $bundles = $this->params->get('kernel.bundles');

        return \array_key_exists('AropixelPageBundle', $bundles);
    }
}

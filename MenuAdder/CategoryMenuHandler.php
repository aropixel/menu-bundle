<?php


namespace Aropixel\MenuBundle\MenuAdder;

use Doctrine\ORM\EntityManagerInterface;
use TickLive\ShopBundle\CentralApi\CentralApi;
use TickLive\ShopBundle\Entity\Category;

class CategoryMenuHandler implements ItemMenuHandlerInterface
{

    /**
     * @var CentralApi
     */
    private $centralApi;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(CentralApi $centralApi, EntityManagerInterface $entityManager)
    {
        $this->centralApi = $centralApi;
        $this->entityManager = $entityManager;
    }

    public function addToMenu(array $menuItems, $type): array
    {
        return $menuItems;
    }

    public function getInputRessources($menuItems): array
    {
        $inputCategoryRessources = [
            'resourceNameSingular' => 'category',
            'ressourceNamePlural' => 'categories',
            'ressourceLabel' => 'Categorie',
            'ressourceColor' => 'bg-purple'
        ];

        $ressources = [];

        $wsCategory = $this->centralApi->getWs(Category::class);
        $categories = $wsCategory->getRootCategories()->toEntities();

        /** @var Category $category */
        foreach ($categories as $category) {

            $ressource = [];

            $ressource['label'] = $category->getLabel();
            $ressource['value'] = $category->getId();
            $ressource['type'] = 'category';
            $ressource['alreadyIncluded'] = false;

            $ressources[$category->getId()] = $ressource;
        }

        asort($ressources);

        $inputCategoryRessources['ressources'] = $ressources;

        return $inputCategoryRessources;
    }


    public function hydrateMenuItem($item, $line): void
    {
        if (isset($item['data']['category'])) {

            /** @var Category $category */
            $category = $this->entityManager->getRepository(Category::class)->find($item['data']['category']);
            $line->setCategory($category);

        }
    }


}

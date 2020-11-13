<?php


namespace Aropixel\MenuBundle\MenuHandler;


use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MenuHandler
{
    /**
    * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    protected $menuHandlers;


    public function __construct(
        iterable $menuHandlers,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    )
    {
        $this->menuHandlers = iterator_to_array($menuHandlers);

        $this->entityManager = $entityManager;
        $this->params = $params;
    }


    /**
     * @param $type
     * @return object[]
     *
     * get All the menu items persisted with the related handler depending on their type (pages etc)
     *
     */
    public function getMenu($type)
    {
        $menuItems = $this->getMenuItems($type);

        foreach ($this->menuHandlers as $menuHandler) {
            $menuItems = $menuHandler->getMenuItems($menuItems, $type);
        }

        return $menuItems;
    }

    /**
     * @param $menuItems
     * @return array
     *
    // get the values for the menu form (pages, link etc)
     */
    public function getInputRessources($menuItems)
    {
        $inputRessources = [];

        foreach ($this->menuHandlers as $menuHandler) {
            if (!empty($menuHandler->getInputRessources($menuItems))) {
                $inputRessources[] = $menuHandler->getInputRessources($menuItems);
            }
        }

        return $inputRessources;
    }

    /**
     * @param $type
     * @param $menuItems
     *
     * save each new menu item
     */
    public function saveMenu($type, $menuItems)
    {
        $linesItems = [];

        foreach ($menuItems as $i => $item) {
            $linesItems[] = $this->saveMenuItem($type, $item);
        }

        $this->entityManager->flush();

        // if items need to be modified after flush (for exemple for sub items, they need to be flushed before we can use it)
        foreach ($this->menuHandlers as $menuHandler) {
            $menuHandler->afterSave($type, $linesItems);
        }

        $this->entityManager->flush();
    }

    /**
     * @param $type
     * @param $item
     * @param Menu|null $parent
     * @return Menu
     *
     * persist a new menu item
     */
    protected function saveMenuItem($type, $item, Menu $parent=null)
    {
        $entity = $this->params->get('aropixel_menu.entity');

        /** @var Menu $line */
        $line = new $entity();
        $line->setType($type);

        //
        if (!is_null($parent)) {
            $line->setParent($parent);
        }

        //
        $title = "";

        foreach ($this->menuHandlers as $menuHandler) {
            $menuHandler->hydrateMenuItem($item, $line);
        }

        //
        if (strlen($item['data']['title'])) {
            $title = $item['data']['title'];
        }

        //
        if (strlen($item['data']['originalTitle'])) {
            $originalTitle = $item['data']['originalTitle'];
            $line->setOriginalTitle($originalTitle);
        }

        $line->setTitle($title);

        //
        if (isset($item['children'])) {
            foreach ($item['children'] as $i => $sbitem) {
                $this->saveMenuItem($type, $sbitem, $line);
            }
        }
        else {
            $this->entityManager->persist($line);
        }

        return $line;
    }

    /**
     * @param $type
     * @return object[]
     *
     * get the current menu items
     */
    protected function getMenuItems($type)
    {
        $entity = $this->params->get('aropixel_menu.entity');
        $menuRepository = $this->entityManager->getRepository($entity);

        $menuRootItems = $menuRepository->findBy(array(
            'parent' => null,
            'type' => $type
        ));

        // TODO : find better way to get Parents with children
        foreach ($menuRootItems as $menuRootItem) {
            $children = $menuRepository->children($menuRootItem, false, [], true);

            if (!empty($children)) {
                $menuRootItem->setChildren($children);
            }
        }

        return $menuRootItems;
    }

}

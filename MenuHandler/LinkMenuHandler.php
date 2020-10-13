<?php

namespace Aropixel\MenuBundle\MenuHandler;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\MenuBundle\Model\MenuInputRessources;

class LinkMenuHandler implements ItemMenuHandlerInterface
{

    /**
     * @param $menuItems
     * @return MenuInputRessources|null
     *
     * get the static and dynamics pages with a class model in order to be displayed into the menu form : empty here
     */
    public function getInputRessources(array $menuItems): ?MenuInputRessources
    {
        return null;
    }

    /**
     * @param array $menuItems
     * @param $type
     * @return array
     *
     * get the current menu link items
     */
    public function getMenuItems(array $menuItems, $type): array
    {
        /** @var Menu $item */
        foreach ($menuItems as $item) {

            // if the menu item is indeed a link
            if ($item->getLink()) {

                // we clean the related link with the host
                $parsing = parse_url($item->getLink());
                if ($parsing) {
                    $item->setLinkDomain($parsing['host']);
                } else {
                    $item->setLinkDomain($item->getLink());
                }
            }

        }

        return $menuItems;
    }

    /**
     * @param $item
     * @param $line
     *
     * add item link info before persisting it
     */
    public function hydrateMenuItem($item, $line): void
    {
        $link = null;

        //
        if ($item['data']['type'] == 'link' && !strlen($item['data']['link'])) {
            $item['data']['link'] = '#';
        }

        //
        if (strlen($item['data']['link'])) {
            $link = $item['data']['link'];
        }

        $line->setLink($link);
    }

    /**
     * @param array $linesItems
     *
     * nothing to do here after save
     */
    public function afterSave($type, array $linesItems): void
    {

    }


}

<?php


namespace Aropixel\MenuBundle\MenuAdder;


use Aropixel\MenuBundle\Entity\Menu;

class LinkMenuHandler
{

    /**
     * @param array $menuItems
     */
    public function setItemsLinkDomain(array $menuItems): void
    {
        // pour chaque item de menu on vérifie globalement si l'item a déjà été ajouté
        // au menu, pour ensuite le bloquer au re-ajout dans le menu
        /** @var Menu $item */
        foreach ($menuItems as $item) {

            // si l'item est un lien
            if ($item->getLink()) {
                // on clean le lien et on le modifie pour l'item
                $parsing = parse_url($item->getLink());
                if ($parsing) {
                    $item->setLinkDomain($parsing['host']);
                } else {
                    $item->setLinkDomain($item->getLink());
                }
            }

        }
    }

}

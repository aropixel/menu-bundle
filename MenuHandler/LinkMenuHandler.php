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
     * récupère les items de liens à afficher pour créer le menu : pas besoin ici (étant donné que c'est un champs texte)
     */
    public function getInputRessources($menuItems): ?MenuInputRessources
    {
        return null;
    }

    /**
     * @param array $menuItems
     * @param $type
     * @return array
     *
     * récupère les items sauvés en bdd du menu actuel
     */
    public function addToMenu(array $menuItems, $type): array
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

        return $menuItems;
    }

    /**
     * @param $item
     * @param $line
     *
     * ajoute les infos pour persister un item link du menu
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

}

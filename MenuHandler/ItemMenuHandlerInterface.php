<?php

namespace Aropixel\MenuBundle\MenuHandler;


use Aropixel\MenuBundle\Model\MenuInputRessources;

interface ItemMenuHandlerInterface
{
    public function addToMenu(array $menuItems, $type): array;

    public function getInputRessources($menuItems): ?MenuInputRessources;

    public function hydrateMenuItem($item, $line): void;
}

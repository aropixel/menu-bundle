<?php

namespace Aropixel\MenuBundle\MenuAdder;


interface ItemMenuHandlerInterface
{
    public function addToMenu(array $menuItems, $type): array;

    public function getInputRessources($menuItems): array;

    public function hydrateMenuItem($item, $line): void;
}

<?php

namespace Aropixel\MenuBundle\MenuHandler;


interface ItemMenuHandlerInterface
{
    public function addToMenu(array $menuItems, $type): array;

    public function getInputRessources($menuItems): array;

    public function hydrateMenuItem($item, $line): void;
}

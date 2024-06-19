<?php

namespace Aropixel\MenuBundle\MenuHandler;


use Aropixel\MenuBundle\Model\MenuInputRessources;

interface ItemMenuHandlerInterface
{
    public function getMenuItems(array $menuItems, $type): array;

    public function getInputRessources(array $menuItems): ?MenuInputRessources;

    public function hydrateMenuItem($item, $line): void;

    public function afterSave($type, array $linesItems): void;
}

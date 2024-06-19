<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\MenuBundle\EventListener;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;


class MappedSuperClassListener
{

    public function __construct(private $entityName)
    {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getReflectionClass()->implementsInterface(MenuInterface::class)) {

            if ($this->entityName == $metadata->getName() && $metadata->isMappedSuperclass) {

                $metadata->isMappedSuperclass = false;

            }

        }
    }

}

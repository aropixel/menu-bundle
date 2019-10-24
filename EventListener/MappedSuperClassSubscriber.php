<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\MenuBundle\EventListener;

use Aropixel\MenuBundle\Entity\Menu;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Webmozart\Assert\Assert;


class MappedSuperClassSubscriber implements EventSubscriber
{

    /** @var string */
    private $entityName;

    /**
     * MapPageBundleSubscriber constructor.
     */
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
    }


    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getName() === $this->entityName) {

            if ($metadata->isMappedSuperclass) {

                $metadata->isMappedSuperclass = false;

            }
        }
    }

}

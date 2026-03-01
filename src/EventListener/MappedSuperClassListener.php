<?php

namespace Aropixel\MenuBundle\EventListener;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 8192)]
class MappedSuperClassListener
{
    public function __construct(
        #[Autowire('%aropixel_menu.entity%')]
        private $entityName
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getReflectionClass()->implementsInterface(MenuInterface::class)) {
            if ($this->entityName === $metadata->getName() && $metadata->isMappedSuperclass) {
                $metadata->isMappedSuperclass = false;
            }
        }
    }
}

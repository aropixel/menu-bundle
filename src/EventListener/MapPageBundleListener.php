<?php

namespace Aropixel\MenuBundle\EventListener;

use Aropixel\PageBundle\Entity\PageInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 8192)]
class MapPageBundleListener
{
    /**
     * @param bool   $isPageEnabled
     * @param string $entityName
     */
    public function __construct(
        #[Autowire('%aropixel_menu.page_active%')]
        private readonly bool $isPageEnabled,
        #[Autowire('%aropixel_menu.entity%')]
        private readonly string $entityName,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if ($this->isPageEnabled && $metadata->getName() === $this->entityName) {
            $metadata->mapManyToOne(['fieldName' => 'page', 'targetEntity' => PageInterface::class]);
        }
    }
}

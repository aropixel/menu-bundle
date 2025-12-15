<?php

namespace Aropixel\MenuBundle\EventListener;


use Aropixel\PageBundle\Entity\PageInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;


class MapPageBundleListener
{

    /**
     * @param bool $isPageEnabled
     * @param string $entityName
     */
    public function __construct(
        private readonly bool $isPageEnabled,
        private readonly string $entityName,
    )
    {
    }


    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if ($this->isPageEnabled && $metadata->getName() == $this->entityName) {

            $metadata->mapManyToOne(['fieldName' => 'page', 'targetEntity' => PageInterface::class]);

        }


    }


}

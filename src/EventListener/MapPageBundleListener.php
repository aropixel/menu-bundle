<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\MenuBundle\EventListener;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
use Aropixel\PageBundle\Entity\PageInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;



class MapPageBundleListener
{

    /**
     * @param bool $isPageEnabled
     * @param string $entityName
     */
    public function __construct(private $isPageEnabled, private $entityName)
    {
    }


    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if ($this->isPageEnabled && $metadata->getName()==$this->entityName) {

            $metadata->mapManyToOne(['fieldName' => 'page', 'targetEntity' => PageInterface::class]);

        }


    }


}

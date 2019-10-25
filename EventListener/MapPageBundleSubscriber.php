<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 15:56
 */

namespace Aropixel\MenuBundle\EventListener;

use Aropixel\MenuBundle\Entity\Menu;
use Aropixel\PageBundle\Entity\Page;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;



class MapPageBundleSubscriber implements EventSubscriber
{

    /** @var boolean */
    private $isPageEnabled;


    /** @var string */
    private $entityName;



    public function __construct($isPageEnabled, $entityName)
    {
        $this->isPageEnabled = $isPageEnabled;
        $this->entityName = $entityName;
    }


    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {

        /** @var ClassMetadataInfo $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if ($this->isPageEnabled && $metadata->getName()==$this->entityName) {

//            $metadata->mapManyToOne(array(
//                'fieldName' => 'page',
//                'targetEntity' => Page::class
//            ));

        }


    }


}

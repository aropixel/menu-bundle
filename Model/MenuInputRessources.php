<?php

namespace Aropixel\MenuBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class MenuInputRessources
{
    private $resourceNameSingular;

    private $ressourceNamePlural;

    private $ressourceLabel;

    private $ressourceColor;

    private $ressources;

    public function __construct()
    {
        $this->ressources = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getResourceNameSingular()
    {
        return $this->resourceNameSingular;
    }

    /**
     * @param mixed $resourceNameSingular
     */
    public function setResourceNameSingular(string $resourceNameSingular): void
    {
        $this->resourceNameSingular = $resourceNameSingular;
    }

    /**
     * @return mixed
     */
    public function getRessourceNamePlural()
    {
        return $this->ressourceNamePlural;
    }

    /**
     * @param mixed $ressourceNamePlural
     */
    public function setRessourceNamePlural(string $ressourceNamePlural): void
    {
        $this->ressourceNamePlural = $ressourceNamePlural;
    }

    /**
     * @return mixed
     */
    public function getRessourceLabel()
    {
        return $this->ressourceLabel;
    }

    /**
     * @param mixed $ressourceLabel
     */
    public function setRessourceLabel(string $ressourceLabel): void
    {
        $this->ressourceLabel = $ressourceLabel;
    }

    /**
     * @return mixed
     */
    public function getRessourceColor()
    {
        return $this->ressourceColor;
    }

    /**
     * @param mixed $ressourceColor
     */
    public function setRessourceColor(string $ressourceColor): void
    {
        $this->ressourceColor = $ressourceColor;
    }

    /**
     * @return mixed
     */
    public function getRessources()
    {
        $criteria = Criteria::create()->orderBy(["label" => Criteria::ASC]);
        return $this->ressources->matching($criteria);
    }


    public function addRessource(MenuInputRessource $ressource)
    {
        if (!$this->ressources->contains($ressource)) {
            $this->ressources->add($ressource);
        }
    }

    /**
     * @param ArrayCollection $ressources
     */
    public function setRessources(ArrayCollection $ressources): void
    {
        $this->ressources = $ressources;
    }


}

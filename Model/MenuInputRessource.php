<?php

namespace Aropixel\MenuBundle\Model;

class MenuInputRessource
{
    private $label;

    private $value;

    private $type;

    private $alreadyIncluded;

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getAlreadyIncluded()
    {
        return $this->alreadyIncluded;
    }

    /**
     * @param mixed $alreadyIncluded
     */
    public function setAlreadyIncluded(bool $alreadyIncluded): void
    {
        $this->alreadyIncluded = $alreadyIncluded;
    }

}

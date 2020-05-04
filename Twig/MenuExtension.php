<?php

declare(strict_types=1);

namespace Aropixel\MenuBundle\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{

    /** @var ParameterBagInterface */
    private $parameterBag;


    /**
     * MenuExtension constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('is_strict_mode', array($this, 'isStrictMode')),
        ];
    }


    public function isStrictMode()
    {
        // permet de récupérer l'entité correcte définis en parametre du front
        $isStrictMode = $this->parameterBag->get('aropixel_menu.strict_mode');

        return $isStrictMode;
    }

}

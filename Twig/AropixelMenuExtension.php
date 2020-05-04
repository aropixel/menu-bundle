<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/05/2019 à 13:32
 */

namespace Aropixel\MenuBundle\Twig;


use Aropixel\MenuBundle\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;


abstract class AropixelMenuExtension extends AbstractExtension
{

    /** @var RequestStack */
    protected $request;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var RequestStack */
    private $requestStack;

    /** @var ParameterBagInterface */
    private $parameterBag;


    /**
     * AropixelMenuExtension constructor.
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $em,
        UrlGeneratorInterface $router,
        ParameterBagInterface $parameterBag
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->parameterBag = $parameterBag;
    }


    public function getFilters()
    {
        return array(
            new TwigFilter('get_link', array($this, 'getLink')),
        );
    }


    public function getFunctions()
    {
        return array(
            new TwigFunction('get_menu', array($this, 'getMenu')),
        );
    }



    public function getMenu($type)
    {
        // permet de récupérer l'entité correcte définis en parametre du front
        $menuEntity = $this->parameterBag->get('aropixel_menu.entity');

        $menuItems = $this->em->getRepository($menuEntity)->findBy(array(
            'parent' => null,
            'type' => $type
        ));

        return $menuItems;
    }


    abstract public function getLink(Menu $menuItem) : string;



}

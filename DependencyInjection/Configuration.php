<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 16/04/2019 à 11:23
 */

namespace Aropixel\MenuBundle\DependencyInjection;

use Aropixel\MenuBundle\Model\Menu;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('aropixel_menu');

        $treeBuilder->getRootNode()
            ->fixXmlConfig('menu')
            ->children()
                ->arrayNode('menus')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('depth')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('static_pages')
                     ->defaultValue(array())
                     ->useAttributeAsKey('name')
                     ->prototype('variable')->end()
                ->end()
            ->end()
        ;


        return $treeBuilder;
    }



}

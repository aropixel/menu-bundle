<?php

namespace Aropixel\MenuBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AropixelMenuExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        //
        $bundles = $container->getParameter('kernel.bundles');
        $isPageBundleActive = array_key_exists('AropixelPageBundle', $bundles);

        //
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //
        $container->setParameter('aropixel_menu.page_active', $isPageBundleActive);
        $container->setParameter('aropixel_menu.menus', $config['menus']);
        $container->setParameter('aropixel_menu.static_pages', $config['static_pages']);
//        $container->setParameter('aropixel_menu.required_pages', $config['required_pages']);
        $container->setParameter('aropixel_menu.entity', $config['entity']);
        $container->setParameter('aropixel_menu.cache.duration', $config['cache']);

        //
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('orm.xml');


    }


}

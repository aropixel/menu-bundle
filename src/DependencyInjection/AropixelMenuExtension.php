<?php

namespace Aropixel\MenuBundle\DependencyInjection;

use Aropixel\MenuBundle\Source\MenuSourceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AropixelMenuExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        $isPageBundleActive = \array_key_exists('AropixelPageBundle', $bundles);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('aropixel_menu.page_active', $isPageBundleActive);
        $container->setParameter('aropixel_menu.menus', $config['menus']);
        $container->setParameter('aropixel_menu.static_pages', $config['static_pages']);
        $container->setParameter('aropixel_menu.entity', $config['entity']);
        $container->setParameter('aropixel_menu.cache.duration', $config['cache']);

        $container->registerForAutoconfiguration(MenuSourceInterface::class)
            ->addTag('aropixel_menu.source')
        ;

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['FrameworkBundle'])) {
            $container->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        __DIR__ . '/../../assets' => '@aropixel/menu-bundle',
                    ],
                ],
            ]);
        }

        if (isset($bundles['StimulusBundle'])) {
            $container->prependExtensionConfig('stimulus', [
                'controller_paths' => [
                    __DIR__ . '/../../assets',
                ],
            ]);
        }
    }
}

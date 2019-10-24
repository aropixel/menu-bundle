<?php
namespace Aropixel\MenuBundle\DependencyInjection\Compiler;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;


class DoctrineTargetEntitiesResolverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {

        try {
            $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $menuClass = $container->getParameter('aropixel_menu.entity');
        $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', [MenuInterface::class, $menuClass, []]);

        if (!$resolveTargetEntityListener->hasTag('doctrine.event_subscriber')) {
            $resolveTargetEntityListener->addTag('doctrine.event_subscriber', ['event' => 'loadClassMetadata']);
        }

    }

}

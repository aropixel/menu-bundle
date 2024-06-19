<?php

namespace Aropixel\MenuBundle;

use Aropixel\MenuBundle\DependencyInjection\Compiler\DoctrineTargetEntitiesResolverPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;



class AropixelMenuBundle extends Bundle {


    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DoctrineTargetEntitiesResolverPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }




}

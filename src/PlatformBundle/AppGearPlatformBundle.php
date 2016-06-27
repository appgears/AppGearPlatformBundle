<?php

namespace AppGear\PlatformBundle;

use AppGear\PlatformBundle\DependencyInjection\Compiler\TaggedCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use AppGear\PlatformBundle\DependencyInjection\Compiler\AtomCompilerPass;

class AppGearPlatformBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container); 

        $container->addCompilerPass(new TaggedCompilerPass());
    }
}

<?php

namespace AppGear\PlatformBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppGearPlatformExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));
        $loader->load('fields.yml');
        $loader->load('markdown.yml');
        $loader->load('services.yml');
        $loader->load('twig.yml');

        // Load namespace map from Composer
        $this->loadComposerNamespaceMap($container);
    }

    /**
     * Load composer namespace map
     *
     * @param ContainerBuilder $container
     */
    protected function loadComposerNamespaceMap(ContainerBuilder $container)
    {
        // Now supports only psr-4
        $map = include $container->getParameter('kernel.root_dir').'/../vendor/composer/autoload_psr4.php';
        $container->setParameter('composer.namespace_map', $map);
    }
}

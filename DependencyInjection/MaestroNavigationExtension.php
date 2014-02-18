<?php

namespace Maestro\Bundle\NavigationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MaestroNavigationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuredMenus = array();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/navigation.yml')) {
                $bundleConfig = Yaml::parse(realpath($file));

                $configuredMenus = $this->mergeConfig($configuredMenus, $bundleConfig);
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // validate menu configurations
        foreach ($configuredMenus as $rootName => $menuConfiguration) {
            $configuration = new NavigationConfiguration();
            $configuration->setMenuRootName($rootName);
            $this->processConfiguration($configuration, array($rootName => $menuConfiguration));
        }

        // Last argument of this service is always the menu configuration
        $container
            ->getDefinition('maestro.menu.builder')
            ->addArgument($configuredMenus);

    }

    /**
     * Merge Bundle Configuration with parsed Menu Configuration
     *
     * @param array $configuredMenus the current bundle menu configuration
     * @param array $config the configuration parsed in the bundle
     *
     * @return array
     */
    protected function mergeConfig(array $configuredMenus, array $config)
    {
        return array_merge($configuredMenus, $config);
    }
}
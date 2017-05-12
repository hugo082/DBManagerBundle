<?php

/*
 * This file is part of the DBManagerBundle package.
 *
 * (c) FOUQUET <https://github.com/hugo082/DBManagerBundle>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hugo Fouquet <hugo.fouquet@epita.fr>
 */

namespace DB\ManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use FQT\DBCoreManagerBundle\DependencyInjection\Configuration as CoreConf;
use DB\ManagerBundle\DependencyInjection\Configuration as Conf;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DBManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadTemplates($config, $container);
        $this->loadViews($config, $container);
        $this->loadLinks($config, $container);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function loadTemplates(array $config, ContainerBuilder $container) {
        $container->setParameter($this->getAlias().'.templates', $config['templates']);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadLinks(array $config, ContainerBuilder $container)
    {
        $views = array();
        foreach ($config['links'] as $view) {
            $this->checkArrayContentOfKey($view, "container");
            $views[$view['action']] = $view;
        }
        $container->setParameter($this->getAlias().'.links', $views);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadViews(array $config, ContainerBuilder $container)
    {
        $views = array();
        foreach ($config['views'] as $view) {
            $this->checkArrayContentOfKey($view, "default_view");
            $this->checkArrayContentOfKey($view, "custom_view");
            $this->checkArrayContentOfKey($view, "container");
            $views[$view['action']] = $view;
        }

        $container->setParameter($this->getAlias().'.views', $views);
    }

    private function checkArrayContentOfKey(array &$array, $key) {
        if (key_exists($key, $array) && !empty($array[$key]))
            return true;
        $array[$key] = null;
        return false;
    }
}

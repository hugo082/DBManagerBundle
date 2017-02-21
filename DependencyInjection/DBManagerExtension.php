<?php

namespace DB\ManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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


        $this->loadViews($config, $container);
        $this->loadEntities($config, $container, $configuration->permissions);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadViews(array $config, ContainerBuilder $container)
    {
        if (isset($config['views']))
            $container->setParameter($this->getAlias().'.views', $config['views']);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $permissions
     */
    private function loadEntities(array $config, ContainerBuilder $container, array $permissions)
    {
        foreach ($config['entities'] as $name => $values) {
            $arr = explode(":", $values['fullName'], 2);
            $values['bundle'] = $arr[0];
            $values['name'] = $arr[1];

            if (!isset($values['fullPath']))
                $values['fullPath'] = $values['bundle']."\\Entity\\".$values['name'];

            if (!isset($values['formType']))
                $values['formType'] = $values['name'] . "Type";

            if (!isset($values['fullFormType']))
                $values['fullFormType'] = $values['bundle'] . "\\Form\\" . $values['formType'];

            $tmp = array();
            foreach ($permissions as $p)
                $tmp[$p] = in_array($p, $values['permission']);
            $values['permission'] = $tmp;

            $config['entities'][$name] = $values;
        }

        if (isset($config['views']))
            $container->setParameter($this->getAlias().'.entities', $config['entities']);
    }
}

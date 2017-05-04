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


        $this->loadViews($config, $container);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function loadViews(array $config, ContainerBuilder $container)
    {
        $config['views'][CoreConf::PERM_EDIT][Conf::DISP_ELEM_FORM] = true;
        $config['views'][CoreConf::PERM_ADD][Conf::DISP_ELEM_FORM] = true;
        $config['views'][CoreConf::PERM_ADD][Conf::DISP_ELEM_LIST] = false;
        $config['views'][CoreConf::PERM_LIST][Conf::DISP_ELEM_LIST] = true;
        $config['views'][CoreConf::PERM_REMOVE][Conf::DISP_ELEM_FORM] = false;
        $config['views'][CoreConf::PERM_REMOVE][Conf::DISP_ELEM_LIST] = false;

        $container->setParameter($this->getAlias().'.views', $config['views']);
    }
}

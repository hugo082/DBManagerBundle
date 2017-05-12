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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use FQT\DBCoreManagerBundle\Core\Action;
use DB\ManagerBundle\Core\ViewMetaData;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public const FORM_VIEW = 'form';
    public const LIST_VIEW = 'list';

    public const DEF_VIEWS = array(
        self::FORM_VIEW => 'DBManagerBundle:Manage:form.html.twig',
        self::LIST_VIEW => 'DBManagerBundle:Manage:list.html.twig'
    );

    public static function getDefaultViewInfoForAction(Action $action) {
        if (in_array($action->id, array("add", "edit")))
            return ViewMetaData::createWithDefaultView($action->id, self::FORM_VIEW);
        elseif (in_array($action->id, array("list")))
            return ViewMetaData::createWithDefaultView($action->id, self::LIST_VIEW);
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('db_manager');

        $this->addViewsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addViewsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('templates')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('index')->defaultValue('DBManagerBundle:Manage:index.html.twig')->end()
                        ->scalarNode('main')->defaultValue('DBManagerBundle:Manage:entity.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('views')
                    ->prototype('array')->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('action')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('default_view')->end()
                            ->scalarNode('custom_view')->end()
                            ->arrayNode('container')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('links')
                    ->prototype('array')->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('action')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('container')->isRequired()->cannotBeEmpty()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

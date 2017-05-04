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

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public const DISP_ELEM_FORM = 'form';
    public const DISP_ELEM_LIST = 'list';
    public const DISP_ELEM_ADDLINK = 'addLink';
    public const DISP_ELEM_REMOVELINK = 'removeLink';
    public const DISP_ELEM_EDITLINK = 'editLink';

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
                ->arrayNode('views')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('indexView')->defaultValue('DBManagerBundle:Manage:index.html.twig')->end() // Auto
                        ->arrayNode('list')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(Configuration::DISP_ELEM_FORM)->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('edit')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode(Configuration::DISP_ELEM_LIST)->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

<?php

namespace DB\ManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('db_manager');

//        $rootNode
//            ->children()
//                ->arrayNode('twitter')
//                    ->children()
//                        ->integerNode('client_id')->end()
//                        ->scalarNode('client_secret')->end()
//                    ->end()
//                ->end() // twitter
//            ->end()
//        ;

        //$supportedValues = array('invalid','nulle');

        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('bundle')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('fullpath')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('formtype')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('permission')
                                ->children()
                                    ->booleanNode('add')->isRequired()->defaultTrue()->end()
                                    ->booleanNode('edit')->isRequired()->defaultTrue()->end()
                                    ->booleanNode('remove')->isRequired()->defaultTrue()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('views')
                    ->children()
                        ->arrayNode('list')
                            ->children()
                                ->booleanNode('add')->isRequired()->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('edit')
                            ->children()
                                ->booleanNode('list')->isRequired()->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
//                ->scalarNode('client_id')
//                    ->validate()
//                        ->ifNotInArray($supportedValues)
//                        ->thenInvalid('c\'est mal')
//                    ->end()
//                ->end()
            ->end()
        ;


        return $treeBuilder;
    }
}

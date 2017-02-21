<?php

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
    public $permissions = array('add', 'edit', 'remove');

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('db_manager');

        $this->addEntitiesSection($rootNode);
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
                        ->arrayNode('list')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('add')->defaultTrue()->end()
                            ->end()
                        ->end()
                        ->arrayNode('edit')
                        ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('list')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addEntitiesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('entities')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('fullName')->isRequired()->cannotBeEmpty()
                                ->validate()
                                ->ifTrue(function ($s) {
                                    return preg_match('#^[a-zA-Z]+Bundle:[a-zA-Z]+$#', $s) !== 1;
                                })
                                ->thenInvalid('Invalid fullName. (Ex: AppBundle:RealName)')
                                ->end()
                            ->end()
                            ->scalarNode('fullPath')->end()     // Auto
                            ->scalarNode('formType')->end()     // Auto
                            ->scalarNode('fullFormType')->end() // Auto
                            ->arrayNode('permission')
                                ->defaultValue($this->permissions)
                                ->prototype('enum')
                                    ->values($this->permissions)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}

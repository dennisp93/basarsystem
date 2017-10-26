<?php
/**
 * Created by PhpStorm.
 * User: dpa
 * Date: 19.10.15
 * Time: 10:18
 */

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
                ->scalarNode('base_url')->cannotBeEmpty()->isRequired()->end()
                ->scalarNode('base_path')->cannotBeEmpty()->isRequired()->end()
            ->end();

        return $treeBuilder;
    }
}
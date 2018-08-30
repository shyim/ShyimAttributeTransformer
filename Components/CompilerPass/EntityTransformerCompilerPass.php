<?php

namespace ShyimAttributeTransformer\Components\CompilerPass;

use Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EntityTransformerCompilerPass implements CompilerPassInterface
{
    use TagReplaceTrait;

    public function process(ContainerBuilder $container)
    {
        $this->replaceArgumentWithTaggedServices($container, 'shyim_media.attribute_transformer', 'shyim_entity_transformer', 2);
    }
}
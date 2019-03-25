<?php

namespace ShyimAttributeTransformer\Components\Entity;

interface EntityTransformer
{
    /**
     * @return string
     */
    public function getEntity() : string;

    /**
     * @return void
     */
    public function resolve();
}

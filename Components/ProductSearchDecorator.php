<?php

namespace ShyimAttributeTransformer\Components;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct;

class ProductSearchDecorator implements ProductSearchInterface
{
    /**
     * @var ProductSearchInterface
     */
    private $core;

    /**
     * @var Converter
     */
    private $converter;

    public function __construct(ProductSearchInterface $core, Converter $converter)
    {

        $this->core = $core;
        $this->converter = $converter;
    }

    /**
     * Creates a search request on the internal search gateway to
     * get the product result for the passed criteria object.
     *
     * @param Criteria $criteria
     * @param Struct\ProductContextInterface $context
     *
     * @return ProductSearchResult
     *
     * @throws \Zend_Cache_Exception
     */
    public function search(Criteria $criteria, Struct\ProductContextInterface $context)
    {
        $result = $this->core->search($criteria, $context);

        $this->convertFacetsAttributes($result->getFacets());

        return $result;
    }

    /**
     * Traverses the facet results structure and handles attribute conversion.
     *
     * @param FacetResultInterface[] $facets
     *
     * @return void
     *
     * @throws \Zend_Cache_Exception
     */
    protected function convertFacetsAttributes(array $facets)
    {
        foreach ($facets as $facet) {
            if($facet instanceof FacetResultGroup) {
                /** @var FacetResultGroup $facet */
                $this->convertFacetsAttributes($facet->getFacetResults());
            }

            if($facet instanceof Struct\Extendable) {
                /** @var Struct\Extendable $subFacet */
                $this->convertStructsAttributes($facet, 'ProductSearch_Facet');

                if(method_exists($facet, 'getValues')) {
                    /** @var ValueListItem $value */
                    foreach ($facet->getValues() as $value) {
                        $this->convertStructsAttributes($value, 'ProductSearch_Facet_Value');
                    }
                }
            }
        }
    }

    /**
     * Converts the attributes of objects existing in the facet results structure.
     *
     * @param FacetResultInterface|ValueListItem $struct
     * @param string $mapping
     *
     * @return void
     *
     * @throws \Zend_Cache_Exception
     */
    protected function convertStructsAttributes($struct, string $mapping)
    {
        if($struct instanceof Struct\Extendable) {
            /** @var Struct\Extendable $ */
            $struct->addAttributes(
                array_filter(
                    $this->converter->convert(
                        $mapping,
                        $struct->jsonSerialize()
                    )['attributes'],
                    'self::filterAttributes'
            ));
        }
    }

    /**
     * @param mixed $i
     *
     * @return bool
     */
    static function filterAttributes($i) : bool
    {
        return $i instanceof Struct\Attribute;
    }
}
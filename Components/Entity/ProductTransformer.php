<?php

namespace ShyimAttributeTransformer\Components\Entity;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use ShyimAttributeTransformer\Components\ModelTransformer;

/**
 * Class ProductTransformer
 * @author Soner Sayakci <shyim@posteo.de>
 */
class ProductTransformer extends ModelTransformer implements EntityTransformer
{
    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var LegacyStructConverter
     */
    private $converter;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * MediaTransformer constructor.
     * @param ListProductServiceInterface $listProductService
     * @param LegacyStructConverter $converter
     * @param ContextServiceInterface $contextService
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(ListProductServiceInterface $listProductService, LegacyStructConverter $converter, ContextServiceInterface $contextService)
    {
        parent::__construct();
        $this->listProductService = $listProductService;
        $this->converter = $converter;
        $this->contextService = $contextService;
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function resolve()
    {
        if (!empty($this->ids)) {
            $products = $this->listProductService->getList($this->ids, $this->contextService->getShopContext());
            $this->ids = [];

            foreach ($products as $product) {
                $this->data[$product->getNumber()] = $this->converter->convertListProductStruct($product);
            }
        }
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return 's_articles';
    }
}

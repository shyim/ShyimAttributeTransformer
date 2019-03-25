<?php


namespace ShyimAttributeTransformer\Components\Entity;


use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use ShyimAttributeTransformer\Components\ModelTransformer;

class ManufacturerTransformer extends ModelTransformer implements EntityTransformer
{

    /**
     * @var ManufacturerServiceInterface
     */
    private $manufacturerService;
    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(ManufacturerServiceInterface $manufacturerService, ContextServiceInterface $contextService, LegacyStructConverter $legacyStructConverter)
    {
        $this->manufacturerService = $manufacturerService;
        $this->legacyStructConverter = $legacyStructConverter;
        $this->contextService = $contextService;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return 's_articles_supplier';
    }

    /**
     * @return void
     */
    public function resolve()
    {
        if (!empty($this->ids)) {
            $manufacturers = $this->manufacturerService->getList($this->ids, $this->contextService->getShopContext());
            $this->ids = [];

            foreach ($manufacturers as $manufacturer) {
                $this->data[$manufacturer->getId()] = $this->legacyStructConverter->convertManufacturerStruct($manufacturer);
            }
        }

    }
}
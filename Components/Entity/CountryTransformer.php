<?php

namespace ShyimAttributeTransformer\Components\Entity;

use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use ShyimAttributeTransformer\Components\ModelTransformer;

class CountryTransformer extends ModelTransformer implements EntityTransformer
{
    /**
     * @var CountryGatewayInterface
     */
    private $countryGateway;

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
     * @param CountryGatewayInterface $countryGateway
     * @param LegacyStructConverter $converter
     * @param ContextServiceInterface $contextService
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(CountryGatewayInterface $countryGateway, LegacyStructConverter $converter, ContextServiceInterface $contextService)
    {
        parent::__construct();
        $this->countryGateway = $countryGateway;
        $this->converter = $converter;
        $this->contextService = $contextService;
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function resolve()
    {
        if (!empty($this->ids)) {
            $countries = $this->countryGateway->getCountries($this->ids, $this->contextService->getShopContext());
            $this->ids = [];

            foreach ($countries as $country) {
                $this->data[$country->getId()] = $this->converter->convertCountryStruct($country);
            }
        }
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return 's_core_countries';
    }
}

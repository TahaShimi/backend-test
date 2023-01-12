<?php
namespace Opeepl\BackendTest\Service;

use Opeepl\BackendTest\Classes\ProviderManager;
/**
 * Main entrypoint for this library.
 */
class ExchangeRateService {

    public $providerManager;
    /**
     * Define exchange rate API
     */
    public function __construct(){
        $this->providerManager = new ProviderManager();
    }
    /**
     * Return all supported currencies
     *
     * @return array<string>
     */
    public function getSupportedCurrencies(): array {
        return $this->providerManager->getProviderSupportedCurrencies();
    }   

    /**
     * Given the $amount in $fromCurrency, it returns the corresponding amount in $toCurrency.
     *
     * @param int $amount
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return float
     */
    public function getExchangeAmount(int $amount, string $fromCurrency, string $toCurrency): float {
        return $this->providerManager->getProviderExchangeAmount($fromCurrency,$toCurrency,$amount);
    }
}

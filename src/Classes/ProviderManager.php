<?php

namespace Opeepl\BackendTest\Classes;

class ProviderManager
{
    private $config;
    private $packConfigs;
    private $provider = "";
    private $end_point;
    private $api_key;
    private $actions;
    private $params = [];
    private $data;

    public function __construct()
    {
        $this->packConfigs = require __DIR__ . '/../Config/env.php';
        $this->setProvider($this->packConfigs['default_provider']);
    }

    public function getProviderSupportedCurrencies()
    {
        //check if currencies up to date
        if (!$this->isCurrenciesUptoDate()) {
            $currencies['last_update'] = time();
            $currencies['data'] = array_keys($this->http_request($this->actions['currencies']['action'], $this->actions['currencies']['method'])['symbols']);
            // save currencies as cache
            $this->setData($currencies);
            if (file_put_contents(__DIR__ . '/../cache/Currencies/' . $this->provider . '.json', json_encode($currencies))) {
                return $currencies['data'];
            }
        }
        //return values from cache
        return $this->data['data'];
    }

    /**
     * @param int $amount
     * @param string $from
     * @param string $to
     * @return float
     */
    public function getProviderExchangeAmount($from, $to, $amount)
    {
        //check if exchanges up to date
        if (!$this->isExchangeUptoDate($from, $to)) {
            $this->params = $this->actions['convert']['params'];
            $exchange = $this->http_request($this->actions['convert']['action'], $this->actions['convert']['method'], ['from' => $from, 'to' => $to, 'amount' => $amount]);
            // save exchanges as cache
            $this->data[$from . '-' . $to] = [
                'from' => $from,
                'to' => $to,
                'rate' => $exchange['info']['rate'],
                'last_update' => time(),
            ];
            file_put_contents(__DIR__ . '/../cache/Exchanges/' . $this->provider . '.json', json_encode($this->data));
            return $exchange['result'];
        }
        //return values from cache
        return $amount * $this->data[$from . '-' . $to]['rate'];
    }

    /**
     * Send HTTP request
     * @param string $url
     * @param string $method
     * @param array $params
     */
    public function http_request($url, $method, $params = [])
    {
        // Get method is the only request type supported for now
        if ($method != 'GET') {
            throw new \Exception("Error HTTP method not supported", 1);
        }
        //check if request params are matched
        if (count(array_diff($this->params, array_keys($params))) > 0) {
            throw new \Exception("Error Missing params to send request", 1);
        }
        //Inject params values in url
        foreach ($params as $key => $param) {
            $url = str_replace('{' . $key . '}', $param, $url);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->end_point . $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:text/plain",
            "apikey:" . $this->api_key
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump('api call');
        return json_decode($result, true);
    }

    /**
     * Set manager provider
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->config = require __DIR__ . '/../Config/providers.php';
        //check if provider supported
        if (!isset($this->config[$provider])) {
            throw new \Exception("Error Provider not supported yet", 1);
        }
        //prepare provider configs
        $this->provider = $provider;
        $this->end_point = $this->config[$provider]['end_point'];
        $this->api_key = $this->config[$provider]['api_key'];
        $this->actions = $this->config[$provider]['actions'];
    }

    /**
     * Check last update of currencies for current provider
     * @return boolean
     */
    public function isCurrenciesUptoDate()
    {
        if (file_exists(__DIR__ . '/../cache/Currencies/' . $this->provider . '.json')) {
            $this->setData(json_decode(file_get_contents(__DIR__ . '/../cache/Currencies/' . $this->provider . '.json'), true));
            if ((time() - $this->data['last_update']) < $this->packConfigs['currencies_exceed_time']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check last update of exchanges for current provider
     * @param string $from
     * @param string $to
     * @return boolean
     */
    public function isExchangeUptoDate($from, $to)
    {
        if (file_exists(__DIR__ . '/../cache/Exchanges/' . $this->provider . '.json')) {
            $this->setData(json_decode(file_get_contents(__DIR__ . '/../cache/Exchanges/' . $this->provider . '.json'), true));
            //check if exchange data exist and last update superior than config time
            if (isset($this->data[$from . '-' . $to]) && (time() - $this->data[$from . '-' . $to]['last_update']) < $this->packConfigs['exchange_exceed_time']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set data of cache or api response
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}

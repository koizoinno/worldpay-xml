<?php namespace Koizoinno\Worldpay;

/**
 * Class WorldPayRequest
 * @package Koizoinno\Worldpay
 */
class WorldPayRequest {

    /**
     * @var
     */
    private $data;
    /**
     * @var
     */
    private $client;

    /**
     * @param                $action
     * @param WorldPayClient $client
     */
    public function __construct($transaction, WorldPayClient $client)
    {
        $this->data['transaction'] = $transaction;
        $this->client = $client;
    }

    /**
     * Magic setter for request fields.  Field names must match
     * field names from WorldPay documentation.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     *  Run the request.
     */
    public function execute() {
        return $this->client->call($this->data);
    }
}
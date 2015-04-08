<?php namespace Koizoinno\Worldpay;

use Guzzle\Http\Client;
use GuzzleHttp\Exception\RequestException;
use SimpleXMLElement;

/**
 * Class WorldPayClient
 * @package Koizoinno\Worldpay
 */
class WorldPayClient {

    // Credit Card Transactions
    const SERVICE_CC_PREAUTH = 1;
    const SERVICE_CC_SALES = 2;
    const SERVICE_CC_POST_CAPTURE = 3;
    const SERVICE_CC_REFUND = 4;
    const SERVICE_CC_VOID = 5;
    const SERVICE_CC_STANDALONE_CREDIT_REFUND = 6;
    const SERVICE_CC_INCREMENTAL_AUTH = 20;
    const SERVICE_CC_PREAUTH_REVERSE = 21;
    const SERVICE_CC_AUTHENTICATION = 30;

    // EBT Transactions
    const SERVICE_EBT_BALANCE_INQUIRY = 32;
    const SERVICE_EBT_CASH_BENEFIT_BALANCE_INQUIRY = 33;
    const SERVICE_EBT_CASH_BENEFIT_RETURN = 34;
    const SERVICE_EBT_CASH_BENEFIT_WITHDRAWL = 35;
    const SERVICE_EBT_CASH_BENEFIT_SALE = 36;
    const SERVICE_EBT_FOODSTAMP_VOUCHER_SALE = 37;
    const SERVICE_EBT_FOODSTAMP_RETURN = 38;
    const SERVICE_EBT_FOODSTAMP_SALE = 39;
    const SERVICE_EBT_FOODSTAMP_BALANCE = 40;
    const SERVICE_EBT_VOID = 41;

    // Debit Card Transactions
    const SERVICE_DEBIT_CARD = 11;
    const SERVICE_DEBIT_CARD_RETURN = 26;

    // Credit Card Batch Settlement
    const SERVICE_BATCH_SETTLEMENT = 24;
    const SERVICE_BATCH_SETTLEMENT_ALL = 25;

    // ACH Transactions
    const SERVICE_ACH_SALE = 2;
    const SERVICE_ACH_CREDIT_REFUND = 4;
    const SERVICE_ACH_VOID = 5;
    const SERVICE_ACH_STANDALONE_CREDIT = 6;

    // Check 21 Transaction
    const SERVICE_CHECK_21_SALE = 27;
    const SERVICE_CHECK_21_VOID = 28;
    const SERVICE_CHECK_21_CREDIT_REFUND = 29;

    // Third Party Check Processing
    const SERVICE_3PCP_EXTENDED_ACH_SALE = 27;
    const SERVICE_3PCP_EXTENDED_ACH_CONSUMER_DISBURSEMENT = 15;
    const SERVICE_3PCP_EXTENDED_ACH_CREDIT = 16;
    const SERVICE_3PCP_EXTENDED_ACH_VOID = 17;
    const SERVICE_3PCP_EXTENDED_ACH_PROFILE_ADD = 31;

    // Transaction Retrieve
    const SERVICE_TRANSACTION_RETRIEVE = 19;

    // Stored Profile
    const SERVICE_PROFILE_ADD = 7;
    const SERVICE_PROFILE_SALE = 8;
    const SERVICE_PROFILE_UPDATE = 9;
    const SERVICE_PROFILE_DELETE = 10;
    const SERVICE_PROFILE_RETRIEVE = 12;
    const SERVICE_PROFILE_CREDIT = 13;
    const SERVICE_PROFILE_IMPORT = 18;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $endpointUrl = 'https://trans.worldpay.us/cgi-bin/ProcessXML.cgi';

    /**
     * The fields in this array
     * @var array
     */
    private $encryptedFields = ['ccnum', 'ckacct'];
    private $desKey;


    /**
     * Setup API credentials.
     *
     * @param      $acctid
     * @param null $subid
     * @param null $merchantpin
     */
    public function setupApi($acctid, $subid = null, $merchantpin = null, $desKey = null)
    {
        $this->config['acctid']      = $acctid;
        $this->config['subid']       = $subid;
        $this->config['merchantpin'] = $merchantpin;
        $this->desKey                = $desKey;
    }

    /**
     * Execute request.
     *
     * @param $xml
     *
     * @return \Guzzle\Http\Message\RequestInterface
     */
    public function call($xml)
    {

        $xml = $this->getRequestBody($xml);

        $httpClient  = new Client($this->endpointUrl);

        $request = $httpClient->post('', ['Content-Type' => 'text/xml; charset=UTF8'], $xml);

        $response = $request->send()->xml();

        return $response->trans_catalog->transaction->outputs;

    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getRequestBody($data)
    {
        // encrypt fields if deskey is set.
        if ($this->desKey !== null) {
            foreach ($data as $field => $value) {
                if (in_array($field, $this->encryptedFields, false)) {
                    $key = pack('H*', $this->desKey);
                    $encryptedValue = mcrypt_encrypt(MCRYPT_3DES, $key, $value, MCRYPT_MODE_ECB);
                    $data[$field]   = bin2hex($encryptedValue);
                }
            }
        }

        $merged = array_merge($this->config, $data);

        return $this->buildXML($merged);
    }

    private function buildXML($data)
    {
        $xmlDoc = new \DOMDocument();

        $root         = $xmlDoc->appendChild($xmlDoc->createElement('interface_driver'));
        $transCatalog = $root->appendChild($xmlDoc->createElement('trans_catalog'));

        $transaction = $transCatalog->appendChild($xmlDoc->createElement('transaction'));
        $transaction
            ->appendChild(
                $xmlDoc->createAttribute('name'))
            ->appendChild(
                $xmlDoc->createTextNode($data['transaction']));

        unset($data['transaction']);

        $inputs = $transaction->appendChild($xmlDoc->createElement('inputs'));

        foreach ($data as $k => $v) {
            $inputs->appendChild($xmlDoc->createElement($k, $v));
        }

        $xmlDoc->formatOutput = true;

        $xml = $xmlDoc->saveXml();

        return $xml;
    }
}
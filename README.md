# worldpay-xml

Simple client for using the WorldPay XML API.  This library has only been tested with the Stored Profile functions, but should work for any request.

##Example

```php

// Create a client.
$client = new WorldpayClient();

// Pass settings via setupApi method.
$client->setupApi($acctId, $acctSubId, $merchantPin, $encryptionKey);

// Create a request and pass request type (creditcard, ach, etc) and pass in the client.
$request = new WorldpayRequest('creditcard', $client);

// Add fields as required by your request.  You do not need to include any of the fields passed via setupApi() or the request type.  You must include all required fields as indicated in the documentation.
$request->service = WorldPayClient::SERVICE_PROFILE_CREDIT;
$request->amount = 500;

// Add other fields as needed.

// Execute the request.
$response = $request->execute();

```

<?php

require_once '../vendor/autoload.php';

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

// check for config file
if (!is_file('config.php')) {
    throw new Exception("The configuration file is missing. Create one based on the config.dist.php file and add the required information.");
}

require_once 'config.php';

try {
    $request = $client->createRequest('GET', 'players');
    $response = $client->send($request)->json(['object' => $config['responseAsObject']]);
    
    // check for error - can be multiple, which do we show?
    if (!empty($response->errors)) {
        
        throw RequestException::create(
            $request, 
            new Response($response->errors[0]->code, [], null, ['reason_phrase' => $response->errors[0]->messages[0]])
        );
    }
} catch (RequestException $e) {
    echo $e->getRequest() . "\n";
    if ($e->hasResponse()) {
        echo $e->getResponse() . "\n";
    }
    exit;
}

var_dump($response);
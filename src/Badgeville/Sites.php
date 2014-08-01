<?php

/*
 * The MIT License
 *
 * Copyright 2014 Joey Rivera <joey1.rivera@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Badgeville;

use Exception;
use InvalidArgumentException;
use BadMethodCallException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

/**
 * This is where everything starts. All calls needs to be made via the site
 * instance. Each site instance is specific to a site id.
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Sites extends ResourceAbstract
{
    protected $resourceName = 'sites';
    
    /**
     * Track the guzzle client for api calls
     * 
     * @var \GuzzleHttp\Client
     */
    protected $client;
    
    public function getResourceName()
    {
        return $this->resourceName;
    }
    
    /**
     * Allow access to client, never known when someone might want direct access
     * 
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }
    
    public function setClient(Client $client)
    {
        $this->client = $client;
        
        return $this;
    }
    
    /**
     * Handles all api calls
     * 
     * @param string $uri
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getRequest($uri, array $params = [])
    {
        // validate uri
        if (!is_string($uri)) {
            throw new InvalidArgumentException("Invalid uri {$uri} submitted.");
        }
        
        // make sure uri isn't absolute - remove first / if there
        if ($uri[0] == '/') {
            $uri = substr($uri, 1);
        }

        try {
            $request = $this->client->createRequest('GET', $uri, ['query' => $params]);
            $response = $this->client->send($request)->json();

            // cairo returns 200 even for errors so check response for error
            // errors array can have multiple, which do we show? create one string for all?
            if (!empty($response['errors'])) {
                throw RequestException::create(
                    $request, 
                    new Response($response['errors'][0]['code'], [], null, ['reason_phrase' => $response['errors'][0]['messages'][0]])
                );
            }
        } catch (RequestException $e) {
            $message = $e->getRequest() . "\n";
            if ($e->hasResponse()) {
                $message .= $e->getResponse() . "\n";
            }
            
            throw new Exception($message);
        }
        
        return $response;
    }
}
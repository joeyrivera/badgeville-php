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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

/**
 * This is where everything starts. All calls needs to be made via the site
 * instance. Each site instance is specific to a site id.
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Site 
{
    protected $client;
    protected $config;
    
    /**
     * Creates guzzle instance
     * 
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->client = new GuzzleClient([
            'base_url' => ["{$config['url']}/{version}/{key}/sites/{site}/", [
                'version' => $config['apiVersion'],
                'key' => $config['apiKey'],
                'site' => $config['siteId']
            ]]
        ]);
        
    }
    
    /**
     * Instanciate a requested resource and inject it this site
     * 
     * @param string $name
     * @param array $params
     * @return \Badgeville\ResourceAbstract
     */
    public function __call($name, array $params = [])
    {
        if (!empty($params)) {
            $params = $params[0];
        }
        
        $newName = __NAMESPACE__. '\\' . ucwords($name);
        $instance = new $newName($this, $params);
        return $instance->setSite($this);
    }
    
    /**
     * Handles all api calls
     * 
     * @param string $uri
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getRequest($uri, $params = [])
    {
        // make sure uri isn't absolute - remove first / if there
        
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
            
            throw new \Exception($message);
        }
        
        return $response;
    }
}

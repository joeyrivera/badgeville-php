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
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

/**
 * This is where everything starts. All calls needs to be made via the site
 * instance. Each site instance is specific to a site id.
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Site implements ResourceInterface
{
    /**
     * Track the guzzle client for api calls
     * 
     * @var \GuzzleHttp\Client
     */
    protected $client;
    
    /**
     * configuration information passed to site
     * 
     * @var array
     */
    protected $config;
    
    /**
     * Creates guzzle instance
     * 
     * @param array $config
     */
    public function __construct(array $params)
    {
        $requiredParams = ['url', 'apiVersion', 'apiKey', 'siteId'];
        
        // validate params
        foreach ($requiredParams as $requiredParam) {
            if (!array_key_exists($requiredParam, $params)) {
                throw new Exception("Missing required parameter {$requiredParam} in construct.");
            }
            
            if (!is_string($params[$requiredParam]) || empty($params[$requiredParam])) {
                throw new InvalidArgumentException("Invalid required parameter {$requiredParam} value {$params[$requiredParam]} in construct.");
            }
        }
        
        $config = [
            'base_url' => [
                "{$params['url']}/{version}/{key}/sites/{site}/", [
                    'version' => $params['apiVersion'],
                    'key' => $params['apiKey'],
                    'site' => $params['siteId']
                ]
            ]
        ];
            
        // check for adapter
        if (!empty($params['adapter'])) {
            $config['adapter'] = $params['adapter'];
        }    
        
        $this->config = $params;
        $this->client = new GuzzleClient($config);
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
        $id = null;
        if (!empty($params)) {
            $id = $params[0];
        }
        
        // figure out namespace to load
        $newName = __NAMESPACE__. '\\' . ucwords($name);
        
        // figure out file path to check
        $filePath = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $newName, $count) . ".php";
        
        // does file exist?
        if (!realpath($filePath)) {
            throw new BadMethodCallException("Unable to find called resource {$name}.");
        }
        
        // always inject site into resource
        return new $newName($this, $id);
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
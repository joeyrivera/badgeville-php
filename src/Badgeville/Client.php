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
 * Description of Client
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Client 
{
    protected $client;
    protected $config;
    
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
    
    public function __call($name, array $params = [])
    {
        list($id, $includes) = $params;
        
        // make sure is valid
        $uri = $name;
        
        // id passed?
        if (!empty($id)) {
            $uri .= "/{$id}";
        }
        
        // includes?
        if (!empty($includes)) {
            $uri .= "?includes=" . implode(',', $includes);
        }
        
        return $this->getRequest($uri);
    }
    
    public function info(array $params = [])
    {
        $uri = '';
        if (!empty($params)) {
            $uri = "?includes=" . implode(',', $params);
        }
        
        return $this->getRequest($uri);
    }
    
    protected function getRequest($uri)
    {
        try {
            $request = $this->client->createRequest('GET', $uri);
            $response = $this->client->send($request)->json(['object' => $this->config['responseAsObject']]);

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
        
        return $response;
    }
}

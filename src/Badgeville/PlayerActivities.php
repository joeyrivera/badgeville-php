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

use Badgeville\Client;

/**
 * Description of Players
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class PlayerActivities 
{
    protected $client;
    protected $parentId;
    protected $data = [];
    
    /**
     * take in client or config
     * @param type $client
     */
    public function __construct($client, $parentId)
    {
        
        if (!$client instanceof Client) {
            throw new \Exception("invalid client");
        }
        
        $this->client = $client;
        $this->parentId = $parentId;
        return $this;
    }
    
    public function setData($data)
    {
        $this->data = $data;
        
        return $this;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function findAll(array $params = [])
    {
        $uri = "players/{$this->parentId}/activities";
         
        $response = $this->client->getRequest($uri, $params);
        
        // convert to our stuff
        $collection = [];
        foreach ($response['activities'] as $activity) {
            $newActivity = clone $this;
            $newActivity->setData($activity);
            $collection[] = $newActivity;
        }
        
        return $collection;
    }
    
    public function find($id)
    {
        
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
}

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
use Badgeville\Player\Activities;

/**
 * Description of Players
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Players 
{
    protected $client;
    protected $data = [];
    
    /**
     * take in client or config
     * @param type $client
     */
    public function __construct($client, $id = null)
    {
        if (!$client instanceof Client) {
            throw new \Exception("invalid client");
        }
        
        $this->client = $client;
        
        $this->data['id'] = $id;
        
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
    
    public function __get($key)
    {
        return $this->data[$key];
    }
    
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    public function findAll(array $params = [])
    {
        $uri = 'players';
         
        $response = $this->client->getRequest($uri, $params);
        
        // convert to our stuff
        $collection = [];
        foreach ($response['players'] as $player) {
            $newPlayer = clone $this;
            $newPlayer->setData($player);
            $collection[] = $newPlayer;
        }
        
        return $collection;
    }
    
    public function find($id, array $params = [])
    {
        $response = $this->client->getRequest("players/{$id}", $params);
        
        $player = clone $this;
        return $player->setData($response['players'][0]);
        
    }
    
    public function create($params)
    {
        $response = $this->client->getRequest("players?do=create&data=" . json_encode($params));
        
        $player = clone $this;
        $player->setData($response['players'][0]);
        
        return $player;
    }
    
    public function save()
    {
        $allowedFields = ['name', 'display_name', 'first_name', 'last_name', 'image', 'admin', 'custom'];
        $data = array_intersect_key($this->data, array_flip($allowedFields));
        
        // need to remove null values
        $data = array_filter($data, function ($value) {
            return is_null($value) ? false : true;
        });
        
        $params = [
            'do' => 'update',
            'data' => json_encode($data, JSON_UNESCAPED_SLASHES)
        ];
        
        $response = $this->client->getRequest("players/{$this->data['id']}", $params);
        
        $player = clone $this;
        $player->setData($response['players'][0]);
        
        return $player;
    }
    
    
    public function activities()
    {
        // make sure we have loaded this guys or have the id first
        return new Activities($this);
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
}

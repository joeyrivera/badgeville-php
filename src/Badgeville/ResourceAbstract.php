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

/**
 * Description of ResourceAbstract
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
abstract class ResourceAbstract 
{
    protected $parent;
    protected $data = [];
    protected $client;
    
    public function __construct($parent, $id = null)
    {
        $this->parent = $parent;
        $this->id = $id;
        return $this;
    }
    
    public function __get($key)
    {
        return $this->data[$key];
    }
    
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    public function __call($name, array $params = [])
    {
        if (!empty($params)) {
            $params = $params[0];
        }
        
        $newName = get_called_class(). '\\' . ucwords($name);
        $instance = new $newName($this, $params);
        return $instance->setClient($this->getClient());
    }
    
    public function getParent()
    {
        return $this->parent;
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
    public function setClient($client)
    {
        $this->client = $client;
        
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
    
    public function find($id, array $params = [])
    {
        $uri = $this->uriBuilder(get_called_class()) . $id;
        $indexName = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $response = $this->getClient()->getRequest($uri, $params);
        
        $item = clone $this;
        return $item->setData($response[$indexName][0]);
    }
    
    public function findAll(array $params = [])
    {
        $uri = $this->uriBuilder(get_called_class());
        $indexName = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $response = $this->getClient()->getRequest($uri, $params);
        
        // convert to our stuff
        $collection = [];
        foreach ($response[$indexName] as $item) {
            $newItem = clone $this;
            $newItem->setData($item);
            $collection[] = $newItem;
        }
        
        return $collection;
    }
    
    protected function uriBuilder($namespace)
    {
        $parts = explode('\\', $namespace);

        // remove first part
        array_shift($parts);

        $uri = '';
        foreach ($parts as $part) {
            $uri .= strtolower($part) . '/';
            
            if (false !== $id = $this->getIdOfParent($part)) {
                $uri .= "{$id}/";
            }
        }
        
        return $uri;
    }
    
    protected function getIdOfParent($parentName)
    {
        $instance = $this;
        do {
            $instance = $instance->getParent();
            $insanceName = get_class($instance);
            
            $currentName = substr($insanceName, -abs(strlen($parentName)));
            
            if ($currentName == $parentName) {
                return $instance->id;
            }
            
        } while (!($instance instanceof \Badgeville\Site));
        
        return false;
    }
    
}

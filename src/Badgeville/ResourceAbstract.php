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
 * All resources extend this class to gain the same functionality.
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
abstract class ResourceAbstract 
{
    /**
     * Track who's the parent owner of this resource
     * @var \Badgeville\ResourceAbstract
     */
    protected $parent;
    
    /**
     * Stores the properties for the resource
     * 
     * @var array 
     */
    protected $data = [];
    
    /**
     * Track site owner for api calls
     * 
     * @var \Badgeville\Site
     */
    protected $site;
    
    /**
     * Called from parent
     * 
     * @param \Badgeville\ResourceAbstract $parent
     * @param string $id
     * @return \Badgeville\ResourceAbstract
     */
    public function __construct($parent, $id = null)
    {
        $this->parent = $parent;
        
        // magic setter
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * to load any resource properties
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key];
    }
    
    /**
     * to set any resource properties
     * 
     * @param string $key
     * @param mixed $value
     * @return \Badgeville\ResourceAbstract
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
        
        return $this;
    }
    
    /**
     * To load children of this resource
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
        
        $newName = get_called_class(). '\\' . ucwords($name);
        $instance = new $newName($this, $params);
        return $instance->setSite($this->getSite());
    }
    
    /**
     * Find this resources' parent
     * 
     * @return \Badgeville\ResourceAbstract
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * array representation of the resource properties
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * Get site root of all parents
     * 
     * @return \Badgeville\Site
     */
    public function getSite()
    {
        return $this->site;
    }
    
    /**
     * To inject site
     * 
     * @param \Badgeville\Site $site
     * @return \Badgeville\ResourceAbstract
     */
    public function setSite($site)
    {
        $this->site = $site;
        
        return $this;
    }
    
    /**
     * Stores all resource properties in array
     * 
     * @param array $data
     * @return \Badgeville\ResourceAbstract
     */
    public function setData($data)
    {
        $this->data = $data;
        
        return $this;
    }
    
    /**
     * Find a resource of this type by id
     * 
     * Finds out who the parents are, if any, and grabs their name and id
     * to generate the uri
     * 
     * @param string $id
     * @param array $params
     * @return \Badgeville\ResourceAbstract
     */
    public function find($id, array $params = [])
    {
        $uri = $this->uriBuilder(get_called_class()) . $id;
        $indexName = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $response = $this->getSite()->getRequest($uri, $params);
        
        $item = clone $this;
        return $item->setData($response[$indexName][0]);
    }
    
    /**
     * Find all resources of this type
     * 
     * @param array $params
     * @return array
     */
    public function findAll(array $params = [])
    {
        $uri = $this->uriBuilder(get_called_class());
        $indexName = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $response = $this->getSite()->getRequest($uri, $params);
        
        // convert to our stuff
        $collection = [];
        foreach ($response[$indexName] as $item) {
            $newItem = clone $this;
            $newItem->setData($item);
            $collection[] = $newItem;
        }
        
        return $collection;
    }
    
    /**
     * Sends a create call to the api and returns an instance of this resource
     * 
     * @param array $params
     * @return \Badgeville\ResourceAbstract
     */
    public function create($params)
    {
        $insanceName = get_called_class();
        $uri = $this->uriBuilder($insanceName) . "?do=create&data=" . json_encode($params);
        $response = $this->getSite()->getRequest($uri);
        
        $currentName = strtolower(substr($insanceName, strrpos($insanceName, '\\') + 1));
        $item = clone $this;
        $item->setData($response[$currentName][0]);
        
        return $item;
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

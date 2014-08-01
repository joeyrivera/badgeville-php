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

namespace Badgeville\Cairo;

use Badgeville\Cairo\Sites;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * All resources extend this class to gain the same functionality.
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
abstract class ResourceAbstract implements ResourceInterface
{
    /**
     * Track who's the parent owner of this resource
     * @var \Badgeville\ResourceAbstract
     */
    protected $parent = null;
    
    /**
     * Stores the properties for the resource
     * 
     * @var array 
     */
    protected $data = [];
    
    abstract public function getResourceName();
    
    /**
     * Called from parent
     * 
     * @param \Badgeville\ResourceInterface $parent
     * @param string $id
     * @return \Badgeville\ResourceAbstract
     */
    public function __construct($id = null, ResourceInterface $parent = null)
    {
        if (!is_null($id) && !is_scalar($id)) {
            throw new InvalidArgumentException("Invalid id passed.");
        }
        
        $this->id = $id;
        $this->parent = $parent;
        
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
     * Instantiate a requested resource and inject it this site
     * 
     * @param string $name
     * @param array $params
     * @return \Badgeville\ResourceAbstract
     */
    public function __call($name, array $params = [])
    {
        // namespace is child of parents namespace, append to end
        $namespace = get_called_class() . '\\' . ucwords($name);
        $newName = get_called_class() . '\\' . ucwords($name);

        // figure out file path to check - strip out badgeville and cairo folders
        $filePath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $newName, $count) . ".php";

        // does file exist?
        if (!realpath($filePath)) {
            throw new BadMethodCallException("Unable to find called resource {$newName}.");
        }
        
        // make sure parent (this) has id else can't create
        if (!$this->id) {
            $name = get_called_class();
            throw new BadMethodCallException("Parent {$name} must have an id specified to create child {$namespace}.");
        }
        
        $id = null;
        if (!empty($params)) {
            $id = $params[0];
        }
        
        // always inject site into resource
        return new $namespace($id, $this);
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
     * @return \Badgeville\Cairo\Sites
     */
    public function getSite()
    {
        $instance = $this;
        while (!$instance instanceof Sites) {
            $instance = $instance->getParent();
        }
        
        return $instance;
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
    public function find($id = null, array $params = [])
    {
        // either an id is passed or is already set
        if (!is_null($id)) {
            $uri = $this->uriBuilder() . '/' . $id;
        } elseif ($this->id) {
            $uri = $this->uriBuilder() . '/' . $this->id;
        } else {
            //error
        }
        
        $response = $this->getSite()->getRequest($uri, $params);

        $item = clone $this;
        return $item->setData($response[$this->getResourceName()][0]);
    }
    
    /**
     * Find all resources of this type
     * 
     * @param array $params
     * @return array
     */
    public function findAll(array $params = [])
    {
        $uri = $this->uriBuilder();
        $response = $this->getSite()->getRequest($uri, $params);

        // convert to our stuff
        $collection = [];
        foreach ($response[$this->getResourceName()] as $item) {
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
    
    /**
     * Builds the uri that will be appended to the end of the default client url
     * 
     * @param string $namespace
     * @return string
     */
    protected function uriBuilder()
    {
        $parts = [$this->getResourceName()];
        $instance = $this;
        
        while (null !== $instance = $instance->getParent()) {
            $parts[] = $instance->id;
            $parts[] = $instance->getResourceName();
        }
        
        return implode('/', array_reverse($parts));
    }
}
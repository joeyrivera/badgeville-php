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

namespace Badgeville\Api\Cairo;

use Badgeville\Api\Cairo\Sites;
use Badgeville\Api\Cairo\Utils\Collection;
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
     * 
     * @var \Badgeville\Api\Cairo\ResourceInterface
     */
    protected $parent = null;
    
    /**
     * Stores the properties for the resource
     * 
     * @var array 
     */
    protected $data = [];
    
    /**
     * Make sure all Resources define this as we use it to render the urls
     */
    abstract public function getResourceName();
    
    /**
     * Create a resource. All resources except for site must have a parent
     * 
     * @todo only allow for site or if called by a ResourceInterface
     * @param string $id
     * @param \Badgeville\Api\Cairo\ResourceInterface $parent
     * @return \Badgeville\Api\Cairo\ResourceInterface
     */
    public function __construct($id = null, ResourceInterface $parent = null)
    {
        if (get_called_class() != 'Badgeville\Api\Cairo\Sites' && is_null($parent)) {
            throw new InvalidArgumentException("Unable to call constructor directly");
        }
        
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
        if (!array_key_exists($key, $this->data)) {
            return null;
        }
        
        return $this->data[$key];
    }
    
    /**
     * to set any resource properties
     * 
     * @param string $key
     * @param mixed $value
     * @return \Badgeville\Api\Cairo\ResourceInterface
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
     * @return \Badgeville\Api\Cairo\ResourceInterface
     */
    public function __call($name, array $params = [])
    {
        // class we need to load is child of parents namespace, append to end
        $classToLoad = get_called_class() . '\\' . ucwords($name);

        // figure out file path to check - strip out namespace and first \
        $startPath = substr(str_replace(__NAMESPACE__, '', $classToLoad), 1);
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $startPath) . ".php";

        // does file exist?
        if (!realpath($filePath)) {
            throw new BadMethodCallException("Unable to find called resource {$classToLoad}.");
        }
        
        // make sure parent (this) has id else can't create
        if (!$this->id) {
            $name = get_called_class();
            throw new BadMethodCallException("Parent {$name} must have an id specified to create child {$classToLoad}.");
        }
        
        // is there an id to set?
        $id = !empty($params) ? $params[0] : null;
        
        // always inject site into resource
        return new $classToLoad($id, $this);
    }
    
    /**
     * Find this resources' parent
     * 
     * @return \Badgeville\Api\Cairo\ResourceInterface
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
     * @return \Badgeville\Api\Cairo\Sites
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
     * @return \Badgeville\Api\Cairo\ResourceInterface
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
     * to generate the uri. If an id is not passed, it'll check to see if it's 
     * already been defined and use it.
     * 
     * @param string $id
     * @param array $params
     * @return \Badgeville\Api\Cairo\ResourceInterface
     */
    public function find($id = null, array $params = [])
    {
        // either an id is passed or is already set
        if (!is_null($id)) {
            $uri = $this->uriBuilder() . '/' . $id;
        } elseif ($this->id) {
            $uri = $this->uriBuilder() . '/' . $this->id;
        } else {
            throw new InvalidArgumentException("An id is required to call find.");
        }
        
        $response = $this->getSite()->getRequest($uri, $params);

        $item = clone $this;
        return $item->setData($response[$this->getResourceName()][0]);
    }
    
    /**
     * Find all resources of this type
     * 
     * Limit can only go up to 30
     * 
     * Included children only come back with 10 items max regardless of what the 
     * limit is set for the parent call.
     * 
     * @param array $params
     * @return \Badgeville\Api\Cairo\Utils\Collection
     */
    public function findAll(array $params = [])
    {
        // add defaults
        $params['with_count'] = "true";
        $params['offset'] = !empty($params['offset']) ? $params['offset'] : 0; // 30 is max limit
        $params['limit'] = !empty($params['limit']) ? $params['limit'] : 30; // 30 is max limit
        
        $uri = $this->uriBuilder();
        $response = $this->getSite()->getRequest($uri, $params);

        // lets get what we need for pagination
        extract($response['_context_info']);

        // convert to our stuff
        $collection = new Collection([], $offset, $limit, $count);
        foreach ($response[$this->getResourceName()] as $item) {
            $newItem = clone $this;
            $newItem->setData($item);
            $collection->append($newItem);
        }
        
        return $collection;
    }
    
    /**
     * Builds the uri that will be appended to the end of the default client url
     * 
     * Travels up the chain of parents to build the uri with their resource name 
     * and id.
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

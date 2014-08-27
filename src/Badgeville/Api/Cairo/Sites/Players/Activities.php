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

namespace Badgeville\Api\Cairo\Sites\Players;

use Badgeville\Api\Cairo\ResourceAbstract;
use \DateTime;
use \InvalidArgumentException;

/**
 * Description of Activities
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Activities extends ResourceAbstract
{
    protected $resourceName = 'activities';
    
    protected $queryable = [
        'id',
        'verb'
    ];
    
    public function getResourceName()
    {
        return $this->resourceName;
    }
    
    /**
     * Creates a new activity for a player
     * 
     * can pass in a verb string as the data or an array of verb and any other 
     * custom attribute customized in the badgeville system for that site and
     * activity
     * 
     * @param mixed $data
     * @return \Badgeville\Api\Cairo\Sites\Players\Activities
     */
    public function create($data, $params = [])
    {
        if (is_array($data) && !array_key_exists('verb', $data)) {
            throw new InvalidArgumentException("The required field verb is missing or not valid.");
        }
        
        // grab the verb to test it
        $data = is_array($data) ? $data : ['verb' => $data];
        
        // clean params up
        $data['verb'] = filter_var($data['verb'], FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

        // make sure we have the required fields covered
        if (empty($data['verb'])) {
            throw new InvalidArgumentException("The required field verb is missing or not valid.");
        }
        
        // setup params
        $params['do'] = 'create';
        $params['data'] = json_encode($data);
        
        $response = $this->getSite()->getRequest($this->uriBuilder(), $params);
        
        $item = clone $this;
        $item->setData($response[$this->getResourceName()][0]);
        
        return $item;
    }
    
    public function updateHistory($type = 'viewed')
    {
        $date = new DateTime();
        
        $params = [
            'do' => 'update_history',
            'data' => json_encode([$type => $date->format(DateTime::ISO8601)])
        ];
        
        $response = $this->getSite()->getRequest($this->uriBuilder() . '/' . $this->id, $params);
        
        return $this->setData($response[$this->getResourceName()][0]);
    }

}
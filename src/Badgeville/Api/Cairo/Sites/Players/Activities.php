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

/**
 * Description of Activities
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Activities extends ResourceAbstract
{
    protected $resourceName = 'activities';
    
    public function getResourceName()
    {
        return $this->resourceName;
    }
    
    /**
     * Creates a new activity for a player
     * 
     * @param array $params
     * @return \Badgeville\Api\Cairo\Sites\Players\Activities
     */
    public function create($params)
    {
        // rules for different properties
        $properties = [
            'verb' => [
                'required',
                'filter' => FILTER_SANITIZE_STRING,
            ]
        ];
        
        // clean params up
        $data = filter_var_array($params, $properties, false);

        // make sure we have the required fields covered
        foreach ($properties as $key => $value) {
            if (isset($value['required']) && $value['required'] === true && empty($data[$key])) {
                throw new Exception("The required field {$key} is missing or not valid.");
            }
        }
        
        $params = [
            'do' => 'create',
            'data' => json_encode($data, JSON_UNESCAPED_SLASHES) // needed or messes up urls
        ];

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
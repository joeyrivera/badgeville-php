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

namespace Badgeville\Api\Cairo\Sites;

use Badgeville\Api\Cairo\ResourceAbstract;
use Exception;

/**
 * Description of Players
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Teams extends ResourceAbstract
{
    protected $resourceName = 'teams';
    
    public function getResourceName()
    {
        return $this->resourceName;
    }
    
    /**
     * Creates a new teams instance
     * 
     * @param array $params
     * @return \Badgeville\Api\Cairo\Sites\Teams
     */
    public function create($params)
    {
        // rules for different properties
        $properties = [
            'display_name' => [
                'required',
                'filter' => FILTER_SANITIZE_STRING,
            ],
            'name' => FILTER_SANITIZE_STRING,
            'active' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => FILTER_NULL_ON_FAILURE
            ],
            'display_priority' => FILTER_SANITIZE_NUMBER_INT
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
    
    /**
     * Updates a team resource
     * 
     * @param \Badgeville\Api\Cairo\Sites\Teams $obj
     * @return \Badgeville\Api\Cairo\Sites\Teams
     */
    public function update($obj = null)
    {
        $useSelf = false;
        if ($obj instanceof $this) {
            $objData = $obj->toArray();
        } else {
            $useSelf = true;
            $objData = $this->data;
        }

        $properties = [
            'display_name' => FILTER_SANITIZE_STRING,
            'name' => FILTER_SANITIZE_STRING,
            'active' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => FILTER_NULL_ON_FAILURE
            ],
            'display_priority' => FILTER_SANITIZE_NUMBER_INT
        ];
        
        // need to remove null values
        $data = array_filter($objData, function ($value) {
            return is_null($value) ? false : true;
        });
        
        // clean params up
        $data = filter_var_array($data, $properties, false);
        
        $params = [
            'do' => 'update',
            'data' => json_encode($data, JSON_UNESCAPED_SLASHES) // needed or messes up urls
        ];

        $response = $this->getSite()->getRequest($this->uriBuilder() . '/' . $this->id, $params);
        
        if ($useSelf) {
            return $this->setData($response[$this->getResourceName()][0]);
        }
        
        $player = clone $this;
        $player->setData();
        
        return $player;
    }
}

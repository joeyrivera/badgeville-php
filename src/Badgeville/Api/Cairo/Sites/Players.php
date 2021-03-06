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
use \Exception;
use \InvalidArgumentException;

/**
 * Description of Players
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Players extends ResourceAbstract
{
    protected $resourceName = 'players';

    public function getResourceName()
    {
        return $this->resourceName;
    }
    
    public function setCustom($key, $value)
    {
        $this->data['custom'][$key] = $value;
        
        return $this;
    }
    
    /**
     * Creates a new players instance
     * 
     * if any data property is passed that is not in the list of properties,
     * it's assumed to be a custom property and passed to Badgeville as such
     * 
     * @param array $params
     * @return \Badgeville\Api\Cairo\Sites\Players
     */
    public function create($data, $params = [])
    {
        // rules for different properties
        $properties = [
            'email' => [
                'required',
                'filter' => FILTER_VALIDATE_EMAIL,
            ],
            'name' => FILTER_SANITIZE_STRING,
            'display_name' => FILTER_SANITIZE_STRING,
            'first_name' => FILTER_SANITIZE_STRING,
            'last_name' => FILTER_SANITIZE_STRING,
            'image' => FILTER_VALIDATE_URL,
            'admin' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => FILTER_NULL_ON_FAILURE
            ]
        ];
        
        // check for custom params, if they aren't in the main list assume custom
        $custom = array_diff_key($data, $properties);
        
        // clean params up
        $data = filter_var_array($data, $properties, false);
        
        // make sure we have the required fields covered
        // required must be within an array for now
        foreach ($properties as $key => $value) {
            if (is_array($value) && in_array('required', $value) && empty($data[$key])) {
                throw new InvalidArgumentException("The required field {$key} is missing or not valid.");
            }
        }
        
        // add in params
        $data = array_merge($data, $params);

        // any custom params to add?
        if (!empty($custom)) {
            $data['custom'] = $custom;
        }
        
        $params = [
            'do' => 'create',
            'data' => json_encode($data, JSON_UNESCAPED_SLASHES) // needed or messes up image urls
        ];

        $response = $this->getSite()->getRequest($this->uriBuilder(), $params);
        
        $item = clone $this;
        $item->setData($response[$this->getResourceName()][0]);
        
        return $item;
    }
    
    /**
     * Updates a player resource
     * 
     * @param \Badgeville\Api\Cairo\Sites\Players $obj
     * @return \Badgeville\Api\Cairo\Sites\Players
     */
    public function update($obj = null, $params = [])
    {
        $useSelf = false;
        if ($obj instanceof $this) {
            $objData = $obj->toArray();
        } else {
            $useSelf = true;
            $objData = $this->data;
        }

        // rules for different properties
        $properties = [
            'name' => FILTER_SANITIZE_STRING,
            'display_name' => FILTER_SANITIZE_STRING,
            'first_name' => FILTER_SANITIZE_STRING,
            'last_name' => FILTER_SANITIZE_STRING,
            'image' => FILTER_VALIDATE_URL,
            'admin' => [
                'filter' => FILTER_VALIDATE_BOOLEAN,
                'flags' => FILTER_NULL_ON_FAILURE
            ],
            //'custom' => FILTER_SANITIZE_STRING
        ];
        
        // need to remove null values
        $data = array_filter($objData, function ($value) {
            return is_null($value) ? false : true;
        });
        
        // clean params up
        $data = filter_var_array($data, $properties, false);
        
        if (!empty($this->data['custom'])) {
            $custom = array_filter($this->data['custom'], function ($value) {
                return is_null($value) ? false : true;
            });
            $data = array_merge($data, ['custom' => $custom]);
        }

        $params = [
            'do' => 'update',
            'data' => json_encode($data, JSON_UNESCAPED_SLASHES) // needed or messes up urls
        ];

        $response = $this->getSite()->getRequest($this->uriBuilder() . '/' . $this->id, $params);
        
        if ($useSelf) {
            return $this->setData($response[$this->getResourceName()][0]);
        }
        
        $player = clone $this;
        $player->setData($response[$this->getResourceName()][0]);
        
        return $player;
    }
    
    /**
     * Allows player to join one or many teams
     * 
     * @param mixed $params can pass in an id or array of ids
     * @return \Badgeville\Api\Cairo\Sites\Players
     */
    public function joinTeams($teamIds)
    {
        $ids = is_array($teamIds) ? $teamIds : [$teamIds];

        $params = [
            'do' => 'join',
            'data' => json_encode(['teams' => $ids])
        ];
        
        $response = $this->getSite()->getRequest($this->uriBuilder() . '/' . $this->id, $params);
        
        return $this->setData($response[$this->getResourceName()][0]);
    }
    
    /**
     * Allows player to leave one or many teams
     * 
     * @param mixed $params can pass in an id or array of ids
     * @return \Badgeville\Api\Cairo\Sites\Players
     */
    public function leaveTeams($ids)
    {
        if (!is_array($ids)) {
            $ids[] = $ids;
        }

        $params = [
            'do' => 'leave',
            'data' => json_encode(['teams' => $ids])
        ];
        
        $response = $this->getSite()->getRequest($this->uriBuilder() . '/' . $this->id, $params);
        
        return $this->setData($response[$this->getResourceName()][0]);
    }
}
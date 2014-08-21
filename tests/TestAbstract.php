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

namespace Badgeville\Test;

use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

/**
 * Description of Abstract
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
abstract class TestAbstract extends \PHPUnit_Framework_TestCase 
{
    /**
     * reusable site
     * 
     * @return \Badgeville\Site
     */
    protected function getValidSite($mockAdapter = null)
    {
        $config = [
            'url' => 'http://www.joeyrivera.com',
            'apiVersion' => 'v1',
            'apiKey' => '123asdf1234asdf',
            'siteId' => '123asdf'
        ];
        
        if (!is_null($mockAdapter)) {
            $config['adapter'] = $mockAdapter;
        }
        
        $site = new \Badgeville\Api\Cairo\Sites($config['siteId']);
        return $site->setClient(new \GuzzleHttp\Client($config));
    }
    
    /**
     * return a valid instance of a resource to test
     * 
     * @todo clean up, don't like how this works
     */
    protected function getValidInstance($mockAdapter = null)
    {
        $site = $this->getValidSite($mockAdapter);
        
        // to split off namespace part until after site
        $string = substr($this->namespace, strlen(get_class($site)) + 2);
        
        if (empty($string)) {
            return $site;
        }
        
        $parts = explode('\\', $string);

        $instance = $site;
        foreach ($parts as $part) {
            $name = strtolower($part);
            $instance = $instance->$name('fakeid');
        }
        
        return $instance;
    }
    
    /**
     * @expectedException \Exception
     */
    public function testConstructExceptionNotDirectly()
    {
        new $this->namespace();
    }
    
    public function testConstruct()
    {
        // dynamically generate string to test
        $this->assertInstanceOf($this->namespace, $this->getValidInstance());
    }
    
    /**
     * make sure the name is lower case alpha
     */
    public function testGetResourceName()
    {
        $this->assertTrue(ctype_lower($this->getValidInstance()->getResourceName()));
        $this->assertTrue(ctype_alpha($this->getValidInstance()->getResourceName()));
    }
    
    public function testGetParent()
    {
        $instance = $this->getValidInstance();
        
        // only should be null for site
        if ($instance instanceof \Badgeville\Api\Cairo\Sites) {
            $this->assertNull($instance->getParent());
        } else {
            $this->assertInstanceOf('\Badgeville\Api\Cairo\ResourceInterface', $instance->getParent());
        }
    }
    
    public function testToArray()
    {
        $instance = $this->getValidInstance();
        $data = $instance->toArray();
        $this->assertInternalType('array', $data);
        
        if ($instance instanceof \Badgeville\Api\Cairo\Sites) {
            $this->assertEquals('123asdf', $instance->id);
            $this->assertEquals('123asdf', $data['id']);
        } else {
            $this->assertEquals('fakeid', $instance->id);
            $this->assertEquals('fakeid', $data['id']);
        }
    }
    
    public function testGetSite()
    {
        $this->assertInstanceOf('\Badgeville\Api\Cairo\Sites', $this->getValidInstance()->getSite());
    }
    
    public function testSetData()
    {
        $instance = $this->getValidInstance();
        $this->assertNull($instance->name);
        
        $instance->setData(['name' => 'testing123']);
        $this->assertEquals('testing123', $instance->name);
        
        $data = $instance->toArray();
        $this->assertEquals('testing123', $data['name']);
    }
}

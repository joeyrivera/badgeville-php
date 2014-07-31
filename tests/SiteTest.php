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
 * Description of Site
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class SiteTest extends \PHPUnit_Framework_TestCase 
{
    private $playersJson = '{"players":[{"id":"53d7dd7ec3fcd8c990006c28","name":"Joey Rivera","display_name":null,"first_name":null,"last_name":null,"image":"https://sandbox.badgeville.com/images/misc/missing/bar/user_nopicture.png","units":{"points":{"id":null,"display_name":"Points","name":"points","abbreviation":"pts","type":"points","order":null,"all":0.0,"year":0.0,"month":0.0,"week":0.0,"day":0.0}}}],"_context_info":{"offset":0,"limit":1}}';
    private $errorJson = '{"errors":[{"code":404,"status":"Not Found","messages":["Invalid route."]}]}';
    /**
     * reusable site
     * 
     * @return \Badgeville\Site
     */
    private function getValidSite($mockAdapter = null)
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
        
        return new \Badgeville\Site($config);
    }
    
    /**
     * @expectedException \Exception
     */
    public function testConstructExceptionNoConfig()
    {
        new \Badgeville\Site();
    }
    
    /**
     * @expectedException \Exception
     */
    public function testConstructExceptionMissingConfigKey()
    {
        new \Badgeville\Site(['test' => false]);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructExceptionInvalidConfigValues()
    {
        new \Badgeville\Site([
            'url' => '',
            'apiVersion' => '',
            'apiKey' => '',
            'siteId' => ''
        ]);
    }
    
    public function testConstruct()
    {
        $site = $this->getValidSite();
        $this->assertInstanceOf('\Badgeville\Site', $site);
        
        $client = $site->getClient();
        $this->assertInstanceOf('\GuzzleHttp\Client', $client);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetRequestExceptionInvalidUrl()
    {
        $site = $this->getValidSite();
        
        $site->getRequest(123);
    }
    
    /**
     * @expectedException \Exception
     */
    public function testGetRequestExceptionErrorInResponse()
    {
        $mockAdapter = new MockAdapter(function (TransactionInterface $trans) {
            $response = new Response(200, [], Stream::factory($this->errorJson));
            
            return $response;
        });
        
        $site = $this->getValidSite($mockAdapter);
        $response = $site->getRequest('invalid');
    }
    
    public function testGetRequest()
    {
        $mockAdapter = new MockAdapter(function (TransactionInterface $trans) {
            $response = new Response(200, [], Stream::factory($this->playersJson));
            
            return $response;
        });
        
        $site = $this->getValidSite($mockAdapter);
        $response = $site->getRequest('players');
        
        $this->assertInternalType('array', $response);
        $this->assertArrayHasKey('players', $response);
        $this->assertArrayHasKey('_context_info', $response);
        $this->assertEquals('Joey Rivera', $response['players'][0]['name']);
        $this->assertArrayHasKey('units', $response['players'][0]);
        $this->assertArrayHasKey('points', $response['players'][0]['units']);
        
        // make sure we don't get any errors from adding a / at the beginning
        $response = $site->getRequest('/players');
    }
    
    /**
     * @expectedException \BadMethodCallException 
     */
    public function testCallExceptionFileNotFound()
    {
        $site = $this->getValidSite();
        $site->invalid();
    }
    
    /**
     * @expectedException \BadMethodCallException 
     */
    public function testCallExceptionFileNotFoundNested()
    {
        $site = $this->getValidSite();
        $site->invalid()->notvalid();
    }
    
    public function testCallPlayers()
    {
        $site = $this->getValidSite();
        $this->assertInstanceOf('Badgeville\Players', $site->players());
    }
    
    public function testCallPlayersActivities()
    {
        $site = $this->getValidSite();
        $this->assertInstanceOf('Badgeville\Players\Activities', $site->players()->activities());
    }
    
    public function testCallPlayersActivitiesBehaviors()
    {
        $site = $this->getValidSite();
        $this->assertInstanceOf('Badgeville\Players\Activities\Behaviors', $site->players()->activities()->behaviors());
    }
}

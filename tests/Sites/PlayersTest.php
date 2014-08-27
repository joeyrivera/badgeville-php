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

namespace Badgeville\Test\Sites;

use Badgeville\Test\TestAbstract;
use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

/**
 * Tests for Players
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class PlayersTest extends TestAbstract
{
    protected $namespace = '\Badgeville\Api\Cairo\Sites\Players';
    protected $resourceName = 'players';
    
    // define here else won't be in the method scope to access
    protected $playersJson1Player = '{"players":[{"id":"53d7dd7ec3fcd8c990006c28","name":"Joey Rivera","display_name":null,"first_name":null,"last_name":null,"image":"https://sandbox.badgeville.com/images/misc/missing/bar/user_nopicture.png","units":{"points":{"id":null,"display_name":"Points","name":"points","abbreviation":"pts","type":"points","order":null,"all":0.0,"year":0.0,"month":0.0,"week":0.0,"day":0.0}}}],"_context_info":{"offset":0,"limit":10,"count":1}}';

    public function testPagination()
    {
        $mockAdapter = new MockAdapter(function (TransactionInterface $trans) {
            return new Response(200, [], Stream::factory($this->playersJson1Player));
        });
        
        $players = $this->getValidInstance($mockAdapter);
        $this->assertInstanceOf($this->namespace, $players);
        
        $collection = $players->findAll();
        $this->assertInstanceOf('\Badgeville\Api\Cairo\Utils\Collection', $collection);
        
        $pagination = $collection->getPagination();
        $this->assertInternalType('object', $pagination);
        $this->assertEquals(0, $pagination->offset);
        $this->assertEquals(10, $pagination->limit);
        $this->assertEquals(1, $pagination->total);
        $this->assertEquals(1, $pagination->page);
        $this->assertEquals(1, $pagination->pages);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateExceptionMissingEmail()
    {
        $params = [
            'display_name' => 'test',
            'first_name' => 'tester',
            'last_name' => 'person',
        ];
        
        $this->getValidInstance()->create($params);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidValues()
    {
        $params = [
            'email' => 'test',
            'name' => 'test',
            'display_name' => 'test',
            'first_name' => 'tester',
            'last_name' => 'person',
        ];
        
        $this->getValidInstance()->create($params);
    }
}
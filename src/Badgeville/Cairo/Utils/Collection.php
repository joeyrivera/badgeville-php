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

namespace Badgeville\Cairo\Utils;

use ArrayIterator;

/**
 * Used to track collections of objects. Mainly used for findAll methods. As an 
 * added bonus it will keep track of pagination information to make it easy to 
 * show pagination information in a view. Finally has a nice toArray method 
 * to render all objects in the collection as an array.
 *
 * @author Joey Rivera <joey1.rivera@gmail.com>
 */
class Collection extends ArrayIterator
{
    protected $iterator;
    protected $offset;
    protected $limit;
    protected $total;
    
    /**
     * Creat a collection instance
     * 
     * @param array $data
     * @param int $offset
     * @param int $limit
     * @param int $total
     */
    public function __construct(array $data = [], $offset = 0, $limit = 0, $total = 0) 
    {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->total = $total;
        
        // init main class
        parent::__construct($data);
    }
    
    /**
     * Gives back a pagination object with helpful information to render a pagination 
     * view.
     * 
     * @return \stdClass
     */
    public function getPagination()
    {
        $o = new \stdClass();
        $o->offset = $this->offset;
        $o->limit = $this->limit;
        $o->total = $this->total;
        $o->page = $this->total ? (int)floor($o->offset / $o->limit) + 1 : 1;
        $o->pages = $this->total ? (int)ceil($o->total / $o->limit) : 1;
        $o->prev = $o->page > 1 ? $o->page - 1 : null;
        
        // if that a valid prev page?
        if ($o->prev > $o->pages) {
            $o->prev = null;
        }
        
        $o->next = $o->page < $o->pages ? $o->page + 1 : null;
        
        // if that a valid prev page?
        if ($o->next > $o->pages) {
            $o->next = null;
        }
        
        $o->start = $o->offset + 1;
        
        if (($o->offset + $o->limit) > $o->total) {
            $o->end = $o->total;
        } else {
            $o->end = $o->limit * $o->page;
        }

        return $o;
    }
    
    /**
     * helper method to loop through and return an collection of arrays instead 
     * of objects.
     * 
     * @return array
     */
    public function toArray()
    {
        $items = [];
        while ($this->valid()) {
            $items[] = $this->current()->toArray();
            $this->next();
        }

        return $items;
    }
}

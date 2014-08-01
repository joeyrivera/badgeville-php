<?php

namespace Badgeville\Cairo\Utils;

use ArrayIterator;

class Collection extends ArrayIterator
{
    protected $iterator;
    protected $offset;
    protected $limit;
    protected $total;
    
    public function __construct($data = [], $offset = 0, $limit = 0, $total = 0) 
    {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->total = $total;
        
        // init main class
        parent::__construct($data);
    }
    
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

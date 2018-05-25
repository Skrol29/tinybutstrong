<?php
    // $Id: spl_examples.php,v 1.1 2009-06-21 17:26:52 fabrice Exp $

    class IteratorImplementation implements Iterator
    {
        public function current()
        {
        }
        public function next()
        {
        }
        public function key()
        {
        }
        public function valid()
        {
        }
        public function rewind()
        {
        }
    }

    class IteratorAggregateImplementation implements IteratorAggregate
    {
        public function getIterator()
        {
        }
    }

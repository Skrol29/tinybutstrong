<?php
    // $Id: spl_examples.php,v 1.1 2009-06-21 17:26:52 fabrice Exp $

    class IteratorImplementation implements Iterator {
        function current() { }
        function next() { }
        function key() { }
        function valid() { }
        function rewind() { }
    }

    class IteratorAggregateImplementation implements IteratorAggregate {
        function getIterator() { }
    }
?>
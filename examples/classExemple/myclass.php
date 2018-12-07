<?php

class myclass
{

    public $title = "My title";

    public function people()
    {
        return [
            ['name' => 'pierre', 'total' => 12],
            ['name' => 'paul', 'total' => 15]
        ];
    }

    /**
     * Tell to TBS how to get the result from the class
     * In this case a call like
     * MergeBlock('peoples',$class, 'people') ;
     * would return $class->people();
     * @param $source not usefull - only because needed by the API
     * @param $query string name - 3rd parameter from MergeBlock
     * @return mixed
     */
    function tbsdb_open(&$source, &$query)
    {
        return $this->$query();
    }

    /**
     * The key method that loop through the recordSet
     * @param $recset array
     * @param $num integer
     * @return array
     */
    function tbsdb_fetch(&$recordSet, $num)
    {
        return isset($recordSet[$num - 1]) ? $recordSet[$num - 1] : false;
    }

    /**
     * no functional need : just because the API wants it.
     */
    function tbsdb_close(&$recset)
    {
    }

}

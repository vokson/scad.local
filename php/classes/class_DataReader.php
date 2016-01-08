<?php

/*
 * Read properties from binary data as per input format
 */

class DataReader {

    private $formatArray; // Array with format
    private $formatString; // String with format

    public function __construct() {
        $this->formatArray = array();
    }

    /*
     * Add format of to decode
     * 
     * @param string $type Type as per PHP pack() specification
     * @param string $name Name to be passed to returned as key of associative array
     * @param int $count Count of objects to be read
     */

    public function addFormat($type, $name = '', $count = 1) {
        $this->formatArray[] = array('type' => $type, 'name' => $name, 'count' => $count);
    }

    /*
     * Make format string from fromat array
     * 
     * @return bool Success
     */

    private function makeFormat() {
        if (count($this->formatArray) > 0) {
            $array = array();
            foreach ($this->formatArray as $e) {
                $array[] = $e['type'] . $e['count'] . $e['name'];
            }
            $this->formatString = implode('/', $array);
            
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /*
     * Read binary data
     * 
     * @param string $data Binary Data
     * 
     * @return mixed[] Unpacked associative array
     */
    public function unpack($data) {
        $this->makeFormat();
        return unpack($this->formatString, $data);
    }

}

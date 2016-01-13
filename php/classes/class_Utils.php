<?php

class Utils {
    /*
     * Return database connection information
     * 
     * @return string[]
     */

    static public function getDSN() {
        return array('dsn' => "mysql:host=localhost;dbname=scad;charset=utf8",
            'username' => 'root', 'password' => 'vokson');
    }

    static public function nameOfDoc28Table() {
        return 'member_group_for_steel';
    }

    /*
     * Change encoding of variable UTF8 <-> CP1251
     * 
     * @param string $value String to be converted
     * @param bool $direction TRUE (CP1251-> UTF8), FALSE (UTF8 -> CP1251)
     * 
     * @return string String with new encoding
     */

    static public function databaseEncoding($value, $direction) {
        if ($direction == TRUE) {
            return iconv('Windows-1251', 'UTF-8', $value);
        }

        if ($direction == FALSE) {
            return iconv('UTF-8', 'Windows-1251', $value);
        }
    }

    /*
     * Convert array into string or vice versa
     * 
     * @param mixed $value String or Array
     * @param bool $direction
     * 
     * @return string String or Array
     */

    static public function databaseList($value, $direction) {
        if ($direction == TRUE) {
            return implode(' ', $value);
        }

        if ($direction == FALSE) {
            return explode(' ', $value);
        }
    }
    
    /*
     * Get class name of object without namespace
     * 
     * @param mixed $obj Object
     * 
     * @return string Class name without namespace
     */

    static public function getClassName($obj) {
        $classname = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

}

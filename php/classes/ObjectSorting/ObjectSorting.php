<?php

namespace php\classes\ObjectSorting;

class ObjectSorting {

    static private $propertyToCompare;

    static public function sortByProperty($objects, $property) {

        foreach ($objects as $e) {
            if (!isset($e->$property)) {
                throw new InvalidPropertyException;
            }
        }
        
         self::$propertyToCompare = $property;

        usort($objects, "self::compareByProperty");

        return $objects;
    }

    private static function compareByProperty($a, $b) {

        $property = self::$propertyToCompare;

        if ($a->$property == $b->$property) {
            return 0;
        }
        return ($a->$property < $b->$property) ? -1 : 1;
    }

}

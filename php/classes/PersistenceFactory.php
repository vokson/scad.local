<?php

namespace php\classes;

use php\classes\DocObjectAssembler;
use php\classes\Doc28PersistenceFactory;

abstract class PersistenceFactory {

    static private function getFactory($className) {
        
        if ($className == 'MemberGroupSteel21') {
            return new Doc28PersistenceFactory();
        }
    }

    static public function getObjectAssembler($className) {
        return new DocObjectAssembler(self::getFactory($className));
    }

//    abstract static function getSelectionFactory();
    abstract static function getInsertFactory();
}

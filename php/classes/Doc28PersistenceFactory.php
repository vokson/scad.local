<?php

namespace php\classes;

use php\classes\Doc28InsertFactory;

class Doc28PersistenceFactory extends PersistenceFactory{

//    static function getSelectionFactory() {
//        return new \mapper\DocumentSelectionFactory;
//    }
//    
    static function getInsertFactory() {
        return new Doc28InsertFactory();
    }

}

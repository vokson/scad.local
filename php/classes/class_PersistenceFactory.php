<?php

/**
 * Description of PersistenceFactory
 *
 * @author Noskov Alexey <vokson@yandex.ru>
 */
abstract class PersistenceFactory {

    static private function getFactory($className) {
        if ($className == 'MemberGroupSteel21') {
            return new Doc28PersistenceFactory();
        }
    }

    static public function getObjectAssembler($className) {
        return new DocObjectAssembler(PersistenceFactory::getFactory($className));
    }

//    abstract static function getSelectionFactory();
    abstract static function getInsertFactory();
}

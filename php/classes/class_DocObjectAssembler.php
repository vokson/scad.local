<?php

/**
 * Description of DocObjectAssembler
 *
 * @author Noskov Alexey <vokson@yandex.ru>
 */
class DocObjectAssembler {

    protected static $PDO;

    // \mapper\PersistenceFactory
    function __construct($factory) {
        $this->factory = $factory;
        if (!isset(self::$PDO)) {
            $dsn = Utils::getDSN();
            if (is_null($dsn)) {
                throw new Exception("DSN ins't created");
            }
//            var_dump($dsn).'<br/>';
            self::$PDO = new PDO($dsn['dsn'], $dsn['username'], $dsn['password']);
            self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//            var_dump(self::$PDO);
        }
    }

    function getStatement($str) {
        if (!isset($this->statements[$str])) {
            $this->statements[$str] = self::$PDO->prepare($str);
        }
        return $this->statements[$str];
    }

//    function find(\mapper\IdentityObject $idobj) {
//
//        \util\Timer::start('find');
//        
//        $selfact = $this->factory->getSelectionFactory();
//        list($selection, $values) = $selfact->newSelection($idobj);
//        
////        echo 'SELECTION = '.$selection.'<br/>';
////        var_dump($values); echo '<br/>';
//        
//        $stmt = $this->getStatement($selection);
////        echo 'Statement ready = '.\util\Timer::show('find').'<br/>';
////        var_dump($stmt);
//        $stmt->execute($values);
////        echo 'Statement executed = '.\util\Timer::show('find').'<br/>';
//        $raw = $stmt->fetchAll();
////        echo 'Receive raw data = '.\util\Timer::show('find').'<br/>';
//        
//        return $this->factory->getCollection($raw);
//    }

    function insert($obj) {
        $insertFactory = $this->factory->getInsertFactory();
        
        list($insert, $values) = $insertFactory->newInsert($obj);

        $stmt = $this->getStatement($insert);
        $stmt->execute($values);
        
        if ($stmt->errorCode() === '00000') {
            return TRUE;
        }
        return FALSE;
    }
    
//    function delete(\mapper\IdentityObject $idobj) {
//        //Вызываем фабрику удалений
//        $delfact = $this->factory->getDeleteFactory();
//        //Готовим выражение
//        list($delete, $values) = $delfact->newDelete($idobj);
//        $stmt = $this->getStatement($delete);
//        //Выполняем операцию
//        $stmt->execute($values);
//        if ($stmt->errorCode() === '00000') {
//            return TRUE;
//        }
//        return FALSE;
//    }

}

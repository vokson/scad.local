<?php

namespace php\classes;

/**
 * Description of InsertFactory
 *
 * @author Noskov Alexey <vokson@yandex.ru>
 */
abstract class InsertFactory {

    abstract function newInsert($obj);

    protected function buildStatement($table, array $fields) {
        $terms = array();
        $qs = array();

        //Формируем запрос
        $query = "INSERT INTO `{$table}` (";
        $query .= implode(',', array_keys($fields));
        $query .= ") VALUES (";
        foreach ($fields as $key => $value) {
            $terms[] = $value;
            $qs[] = '?';
        }
        $query .= implode(',', $qs);
        $query .= ')';
//        echo $query;
        return array($query, $terms);
    }

}

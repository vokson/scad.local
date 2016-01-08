<?php

/**
 * Export DOC 28 object into database
 *
 
 */
class Doc28InsertFactory extends InsertFactory {

    /*
     * Insert object
     * 
     * @param MemberGroupSteel21 $obj Object to be exported
     * 
     * @return string Database query
     */
    public function newInsert($obj) {
            
            //Получаем имя таблицы
            $table = Utils::nameOfDoc28Table();
            
            $queryPropertyArray = array();

            $properties = get_object_vars($object);
            foreach ($properties as $key => $value) {
                // If there is database action for the property
                if (isset(Utils::$databaseAction[$key])) {
                    $function = $this->databaseAction[$key];
                    $value = $this->$function($value, TRUE);
                }
                // Add to query array
                $queryPropertyArray[] = "$key = '$value'";
            }
            
            $values = array();
            $values['uin'] = $obj->getUin();
            $values['project'] = $obj->getProject();
            $values ['drawing'] = $obj->getDrawing();
            $values['revision'] = $obj->getRevision();
            $values['part'] = $obj->getPart();
            $values['drw_status'] = $obj->getStatus();
            $values['title'] = $obj->getTitle();
            $values['date_in'] = $obj->getDate_In();
            $values['transmit_in'] = $obj->getTransmit_In();
            $values['dwg'] = $obj->getIs_DWG();
            $values['path'] = $obj->getPath();
            
//            var_dump($this->buildStatement($table, $values));
            
            return $this->buildStatement($table, $values);
        }

}

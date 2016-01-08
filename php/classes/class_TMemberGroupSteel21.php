<?php

class TMemberGroupSteel21 {
    
    const databaseAction = [
        'steel' => 'databaseEncoding'
    ];
    
    /*
     * Read Document No.28. Upload it into database.
     *
     * @param string $data Binary data
     */
    function read($data) {

        // Курсор для чтения строки
        $pos = 0;
        
        // Пропускаем неизвестный символ
        $pos += 1;

        // Кол-во групп
        list(, $count) = unpack("I", substr($data, $pos, 4));
        $pos += 4;

        // Пропускаем кол-во байт в блоке list
//        $listBytesCount = unpack("I", substr($data, $pos, 4));
        $pos += 4;

        // Массив описаний групп
        $groups = array();

        //Читаем description блок каждой группы блоками по 329 байт
        for ($i = 0; $i < $count; $i++) {
            $group = new MemberGroupSteel21();
            // Читаем 
            $group->readSingleDescriptionBlock($data, $pos);
            
            $groups[$i] = $group;
        }
        
        //Читаем list блок каждой группы
        for ($i = 0; $i < $count; $i++) {
            // Читаем
            $groups[$i]->readSingleListBlock($data, $pos);
        }
        
        // Отправляем группы в базу данных
        $this->clearDatabase();
        for ($i = 0; $i < $count; $i++) {
            $this->writeDatabase($groups[$i]);
        }
    }
    
    /*
     * Clear database
     */
    private function clearDatabase() {
        mysql_query("TRUNCATE TABLE " . member_group_for_steel);
    }

    /*
     * Write steel group into database
     * 
     * @param MemberGroupSteel21 $object Group object
     */

    private function writeDatabase($object) {
        
        $queryPropertyArray = array();
        
        $properties = get_object_vars($object);
        foreach ($properties as $key => $value ) {
            // If there is database action for the property
            if (isset(self::$databaseAction[$key])) {
                $function = self::$databaseAction[$key];
                $value = $this->$function($value, TRUE);
            }
            // Add to query array
            $queryPropertyArray[] = "$key = '$value'";
        }
        
        $query = "INSERT IGNORE INTO " . member_group_for_steel . " SET " .
                implode(',', $queryPropertyArray);
        
        mysql_query($query);

//        //запись в базу данных
//        mysql_query("INSERT IGNORE INTO " . member_group_for_steel . " SET
//                      steel = '$object->steel_type',
//                      Ry = '$object->steel_Ry',
//                          
//                      group_type = '$object->group_type',
//                      member_type = '$object->member_type',
//                          
//                      isMuReg = '$object->isMuSameWithRegulation',
//                      isMuUsed = '$object->isMuUsed',
//                      onlyElastic = '$object->isOnlyElastic',
//                      addGroup = '$object->isGroupAdditional',
//                      check_DAL = '$object->deflectionFromAllLoadsToBeChecked',
//                      check_DTL = '$object->deflectionFromTemporaryLoadsToBeChecked',
//                          
//                      limit_RDAL = '$object->limitRelativeDisplacementFromAllLoads',
//                      limit_RDTL = '$object->limitRelativeDisplacementFromTemporaryLoads',
//                      limit_ADAL = '$object->limitAbsoluteDisplacementFromAllLoads',
//                      limit_ADTL = '$object->limitAbsoluteDisplacementFromTemporaryLoads',
//                          
//                      gamma_n = '$object->gamma_n',                        
//                      gamma_c = '$object->gamma_c',
//                          
//                      FC = '$object->flexCompressed',
//                      FT = '$object->flexTensed',
//                      BD = '$object->bucklingDistance',    
//
//                      mu_XZ = '$object->mu_XZ',
//                      mu_XY = '$object->mu_XY',
//                      length_XZ = '$object->length_XZ',
//                      length_XY = '$object->length_XY',
//                          
//                      name = '" . iconv('Windows-1251', 'UTF-8', $object->name) . "',
//                      list = '" . implode(' ', $object->list) . "'"
//        );
    }

    // создает документ с группами для подбора стали
    // SCAD в файле SPR
    function set_to_scad_spr() {
        $s = '';
        //формируем начало 28 документа
        //класс стали - 80 байт
        $s .= pack('a80', 'C255');
        //сопротивление стали
        $s .= pack('d', 240.26292);
        //0.95 - неизвестно что
        $s .= pack('d', 0.95);
        //gamma_C
        $s .= pack('d', 0.95);
        //гибкость
        $s .= pack('d', 400);
        //нулевой байт
        $s .= pack('a1', '');

        $sql = "SELECT * FROM " . member_group_for_steel;
        mysql_query($sql);
        switch (mysql_errno()) {
            case 1146: echo "<b>Table " . member_group_for_steel . " doesn't exist. Please create DB.</b><br>";
                break;
            default:
                if (mysql_errno() > 0)
                    echo mysql_errno() . '  ' . mysql_error() . '<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    //количество групп
                    $s .= pack('V', mysql_num_rows($result));
                    while ($row = mysql_fetch_object($result, 'MemberGroupSteel11')) {
                        $row->list = explode(' ', $row->list);
                        $s .= $row->set_to_spr();
                    }
                }
        }
        return $s;
    }

}

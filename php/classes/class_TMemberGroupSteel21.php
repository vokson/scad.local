<?php

class TMemberGroupSteel21 {

    // разбирает документ с группами подбора полученный из
    // SCAD21 в файле SPR
    // Спецификация
    // Начало 28 документа
//    кол-во    Тип   Описание
//     байт 
//      1     int     Неизвестно (возможно всегда == 3)
//      4     int     Количество КО или групп КО
//      4     int     Количество байт описания групп (имена + списки элементов)
//                      за вычетом байтов на имена
//    *** КО или группы КО блоками по 329 байт ***
//      80    string  Тип стали (например, "C255" + остальные нулевые; Другая - все нулевые)
//      1     int     0 - КО, 1 - группа КО
//      1     int     Тип конструктивного элемента
//                      0 - Элемент общего вида
//                      1 - Стойка
//                      2 - Балка
//                      3 - Элемент пояса фермы
//                      4 - Элемент решетки фермы
//                      5 - Элемент раскоса фермы
//                      6 - Опорная стойка фермы
//                      
//      3     -       Нулевые байты
//      1     bool    Коэффициент расчетной длины отличается от нормативных (0 - да, 1 - нет)
//      1     bool    00 - расчетные длины, 01 - коэффициенты расчетной длины
//      1     bool    Неупругая работа (0 - нет, 1 - да)
//      8     double  Cопротивление стали Ry в Н/мм2 (defaul = 0 Н/mm2)
//      8     double  gamma_N
//      8     double  gamma_C
//      8     -       Нулевые байты  
//      8     double  Коэффициент расчетной длины XoZ
//      8     double  Коэффициент расчетной длины XoY
//      8     double  Гибкость сжатых элементов
//      8     double  Гибкость растянутых элементов
//      8     double  Расстояние между точками раскрепления
//      16     -      Нулевые байты  
//      8     double  Расчетная длина (м) XoZ
//      8     double  Расчетная длина (м) XoY
//      8     double  Неизвестно
//      8     double  Неизвестно
//      8     -       Нулевые байты
//      1     bool    Проверка перемещений от всех нагрузок (0 - да, 1 - нет)
//      1     bool    Проверка перемещений от временных нагрузок (0 - да, 1 - нет)  
//      32     -      Нулевые байты
//      4     float   Предельные относительные перемещения от всех нагрузок
//      4     float   Предельные относительные перемещения от временных нагрузок
//      22     -      Нулевые байты
//      1     bool    Дополнительная группа (0 - нет, 1 - да)
//      40     -      Нулевые байты
//      4     float   Предельные абсолютные перемещения (м) от всех нагрузок
//      4     float   Предельные абсолютные перемещения (м) от временных нагрузок
//    *** Описание имен и списком КО ***
//      ?     string  Имя
//      1     -       Нулевой байт
//      4     int     Количество элементов в списке КО
//      4*N   int     Номера N элементов

    function get_from_scad_spr($data) {
        mysql_query("TRUNCATE TABLE " . member_group_for_steel);


        // кол-во групп
        $groupCount = unpackInt_4(substr($data, 1, 4));

        // Количество байт описания групп (имена + списки элементов) за вычетом байтов на имена
        $listBytes = unpackInt_4(substr($data, 5, 4));

        // Получаем блоки КО и описания КО
        $groupData = substr($data, 9, 329 * $groupCount);
        $listData = substr($data, 9 + 329 * $groupCount);

        // Позиция имени первого КО
        $listOffset = 0;

        // КО или группы КО блоками по 329 байт
        for ($i = 0; $i < $groupCount; $i++) {

            $group = new MemberGroupSteel21();

            // Разбираем КО
            $group->get_from_spr(substr($groupData, $i * 329, 329));

            //кол-во байт в названии группы (ищем первое вхождение нулевого байта)
            $groupNameByteCount = strpos($listData, "\x00", $listOffset) - $listOffset;

            // Читаем имя КО
            $group->name = substr($listData, $listOffset, $groupNameByteCount);
            $listOffset += $groupNameByteCount + 1;
            $listBytes -= $groupNameByteCount + 1;

            //кол-во элементов
            $member_count = unpackInt_4(substr($listData, $listOffset, 4));
            $listOffset += 4;

            // Читаем список элементов
            for ($k = 0; $k < $member_count; $k++) {
                $group->list[] = unpackInt_4(substr($listData, $listOffset, 4));
                $listOffset += 4;
                $listBytes -= 4;
            }

            //запись в базу данных
            mysql_query("INSERT IGNORE INTO " . member_group_for_steel . " SET
                      steel = '$group->steel_type',
                      Ry = '$group->steel_Ry',
                          
                      group_type = '$group->group_type',
                      member_type = '$group->member_type',
                          
                      isMuReg = '$group->isMuSameWithRegulation',
                      isMuUsed = '$group->isMuUsed',
                      onlyElastic = '$group->isOnlyElastic',
                      addGroup = '$group->isGroupAdditional',
                      check_DAL = '$group->deflectionFromAllLoadsToBeChecked',
                      check_DTL = '$group->deflectionFromTemporaryLoadsToBeChecked',
                          
                      limit_RDAL = '$group->limitRelativeDisplacementFromAllLoads',
                      limit_RDTL = '$group->limitRelativeDisplacementFromTemporaryLoads',
                      limit_ADAL = '$group->limitAbsoluteDisplacementFromAllLoads',
                      limit_ADTL = '$group->limitAbsoluteDisplacementFromTemporaryLoads',
                          
                      gamma_n = '$group->gamma_n',                        
                      gamma_c = '$group->gamma_c',
                          
                      FC = '$group->flexCompressed',
                      FT = '$group->flexTensed',
                      BD = '$group->bucklingDistance',    

                      mu_XZ = '$group->mu_XZ',
                      mu_XY = '$group->mu_XY',
                      length_XZ = '$group->length_XZ',
                      length_XY = '$group->length_XY',
                          
                      name = '" . iconv('Windows-1251', 'UTF-8', $group->name) . "',
                      list = '" . implode(' ', $group->list) . "'"
            );
        }

        if ($listBytes != 0) {
            echo "ERROR in DOC28 <br/>";
        }
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

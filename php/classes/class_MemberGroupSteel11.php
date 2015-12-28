<?php
require_once dirname(__FILE__) .DIRECTORY_SEPARATOR.  "class_MemberList.php";

class MemberGroupSteel11 extends MemberList {
    public $steel; //Сталь
    public $Ry; //Сопротивление стали в Н/мм2
    public $gamma_c; //Гамма С
    public $FC; //Гибкость
    public $group_type; //0 - констр эл-т, 1 - группа эл-т
    public $mu_XZ; //Коэффициент расчетной длины в пл-ти XoZ
    public $mu_XY; //Коэффициент расчетной длины в пл-ти XoY
    public $name; //имя

    
        /**
    * разбираем группу для подбора арматуры
    * Документ 28
//    2   int     кол-во байт в названии группы
//    x   string  название группы
//    80  string  C255, остальные нулевые
//    8   double  сопротивление стали
//    8   -       нулевые байты
//    8   double  gamma_C
//    8   double  FC
//    1   -       1-группа, 0-конструктивный элемент
//    2   -       нулевые байты
//    8   double  коэффициент расчетной длины XoZ
//    8   double  коэффициент расчетной длины XoY
//    64  -       нулевые байты
//    4   int     кол-во элементов
//    x   int     номера элементов друг за другом по 4 байта    
    *
    * @param String $s
    * @return Array
    */
    function get_from_spr ($s) {
        $pos = 0;
        //кол-во байт в названии группы
        $group_name_byte_count = unpackInt_2(substr($s, $pos, 2)); $pos +=2;
        //название группы
        $this->name = (string)substr($s, $pos, $group_name_byte_count);
        $pos += $group_name_byte_count;
        //класс стали, используем только 10 символов из 80
        $this->steel = (string)substr($s, $pos, 10); $pos += 80;
        //сопротивление стали
        $this->Ry = unpackDouble(substr($s,$pos,8)); $pos += 8;
        //пропускаем пустые 8 байт
        $pos += 8;
        //gamma_C
        $this->gamma_c = unpackDouble(substr($s,$pos,8)); $pos += 8;
        //гибкость
        $this->FC = unpackDouble(substr($s,$pos,8)); $pos += 8;
        //тип группы, пропуск 2-х пустых 
        $this->group_type = unpackInt_1(substr($s, $pos, 1)); $pos += 3;
        //коэффициенты расчетной длины
        $this->mu_XZ = unpackDouble(substr($s,$pos,8)); $pos += 8;
        $this->mu_XY = unpackDouble(substr($s,$pos,8)); $pos += 8;
        //пропускаем пустые 64 байта
        $pos += 64;
        //кол-во элементов
        $member_count = unpackInt_4(substr($s, $pos, 4)); $pos +=4;
        //номера элементов
        $this->list = array();
        for ($i=1;$i<=$member_count;$i++) {
            $this->list[] = unpackInt_4(substr($s, $pos, 4)); $pos +=4;
        }
    }

    
    /**
    * разбираем группу элементов и группы для подбора арматуры
    * Документ 28
    * Пример
    * "C245" 240.262      0.    0.95    400.
    * 1     1.     0.
    * 0     0.     0.     0.     0.     0.     0.     0.      0      0
    * Name="+4.000 - B3_1" : 54 55 99 102 103 106 107 110
    *
    * @param String $s
    * @return Array
    */
    function get_from_txt ($s) {
        $s = trim($s);
        $s = explode("\r\n", $s);
//        print_r($s).'<br/>';
        
        //Разбираем 1 строку
        preg_match('|\s*"(.*)"\s+([\d\.]+)\s+([\d\.]+)\s+([\d\.]+)\s+([\d\.]+)|', $s[0], $matches);
        $this->steel = (string)$matches[1];
//        echo 'STEEL_TYPE = '.  $this->steel.'<br/>';
        $this->Ry = (float)$matches[2];
//        echo 'STEEL_Ry = '.  $this->Ry.'<br/>';
        $this->gamma_c = (float)$matches[4];
//        echo 'G_C = '.  $this->gamma_c.'<br/>';
        $this->FC = (float)$matches[5];
//        echo 'FLEX = '.  $this->FC.'<br/>';

        //Разбираем 2 строку
        preg_match('|\s*(\d+)\s+([\d\.]+)\s+([\d\.]+)|', $s[1], $matches);
        $this->group_type = (boolean)$matches[1];
//        echo 'GROUP_TYPE = '.  $this->group_type.'<br/>';
        $this->mu_XZ = (float)$matches[2];
//        echo 'MU_XZ = '.  $this->mu_XZ.'<br/>';
        $this->mu_XY = (float)$matches[3];
//        echo 'MU_XY = '.  $this->mu_XY.'<br/>';

        //Разбираем оставшиеся строки
        array_splice($s, 0, 3); //удаляем первые 3 строки
        $s = implode(' ',$s); //объединяем оставшиеся в строку
        preg_match('|\s*Name="(.*)"\s+:\s+(.*)|', $s, $matches);
        $this->name = (string)$matches[1];
//        echo 'NAME = '.  $this->name.'<br/>';
        $this->list = (array)$this->get_member_list($matches[2]);
//        echo 'LIST = '.implode(';',$this->list).'<br/>';

//        echo '<br/>';
    }
    
    
    /**
    * создает группу для подбора арматуры
    * Документ 28
    * Пример
    *
    * @param Integer $id
    * @return String
    */
    function set_to_spr () {
        //создаем строку в бинарном виде
        $s = '';
        $nameCP1251 = iconv('UTF-8', 'Windows-1251', $this->name);
        //кол-во байт в названии группы
        $s .= pack('v',strlen($nameCP1251));
        //название группы
        $s .= $nameCP1251;
        //класс стали - 80 байт
        $s .= pack('a80', (string)$this->steel);
        //сопротивление стали
        $s .= pack('d',(double)$this->Ry);
        //8 нулевых байтов
        $s .= pack('a8','');
        //gamma_C
        $s .= pack('d',(double)$this->gamma_c);
        //гибкость
        $s .= pack('d',(double)$this->FC);
        //тип группы
        $s .= chr((boolean)$this->group_type);
        //2 нулевых байта
        $s .= pack('a2','');
        //коэффициенты расчетной длины
        $s .= pack('d',(double)$this->mu_XZ);
        $s .= pack('d',(double)$this->mu_XY);
        //64 нулевых байта
        $s .= pack('a64','');
        //кол-во элементов в группе
        $s .= pack('V', (int)count($this->list));
        //номера элементов
        foreach ($this->list as $member) {
            $s .= pack('V', (int)$member);
        }
        return $s;
    }
    
    
}
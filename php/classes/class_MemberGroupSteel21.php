<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "class_MemberList.php";

class MemberGroupSteel21 extends MemberList {

    public $steel_type; // Сталь
    public $steel_Ry; // Сопротивление стали в Н/мм2
    public $group_type; // 0 - констр эл-т, 1 - группа эл-т
    public $member_type; // Тип конструктивного элемента (0-6)
    public $isMuSameWithRegulation; // 0 - нет, 1 - да
    public $isMuUsed; // 0 - нет, 1 - да
    public $isOnlyElastic; // 0 - нет, 1 - да
    public $isGroupAdditional; // 0 - нет, 1 - да
    public $deflectionFromAllLoadsToBeChecked; // 0 - нет, 1 - да
    public $deflectionFromTemporaryLoadsToBeChecked; // 0 - нет, 1 - да
    public $limitRelativeDisplacementFromAllLoads; // Предельные относительные перемещения от всех нагрузок
    public $limitRelativeDisplacementFromTemporaryLoads; // Предельные относительные перемещения от временных нагрузок
    public $limitAbsoluteDisplacementFromAllLoads; // Предельные абсолютные перемещения от всех нагрузок
    public $limitAbsoluteDisplacementFromTemporaryLoads; // Предельные абсолютные перемещения от временных нагрузок
    public $gamma_n; // Гамма N
    public $gamma_c; // Гамма С
    public $flexCompressed; // Гибкость сжатых элементов
    public $flexTensed; // Гибкость растянутых элементов
    public $bucklingDistance; // Расстояние между точками раскрепления
    public $mu_XZ; //Коэффициент расчетной длины в пл-ти XoZ
    public $mu_XY; //Коэффициент расчетной длины в пл-ти XoY
    public $length_XZ; //Расчетная длина в пл-ти XoZ
    public $length_XY; //Расчетная длина в пл-ти XoY
    public $name; //имя
    public $list; // список элементов

    public function __construct() {
        $this->list = array();
    }

    /*
     * Read single description block
     * 
     * @param string $data Binary data
     * @param int $pos Position of cursost in data
     */

    function readSingleDescriptionBlock($data, &$pos) {

        // класс стали
        $this->steel_type = $this->readCharArray($data, $pos);
        $pos += 80 - strlen($this->steel_type);

        // Тип группы
        list(, $this->group_type) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // Тип элемента
        list(, $this->member_type) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // Нулевые байты
        $pos += 3;

        // Коэффициент расчетной длины отличается от нормативных
        list(, $this->isMuSameWithRegulation) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // 00 - расчетные длины, 01 - коэффициенты расчетной длины
        list(, $this->isMuUsed) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // Неупругая работа (0 - нет, 1 - да)
        list(, $this->isOnlyElastic) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // сопротивление стали
        list(, $this->steel_Ry) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        //gamma_N
        list(, $this->gamma_n) = unpack("d", substr($data, $pos, 8));
        $pos += 8;
        //gamma_C
        list(, $this->gamma_c) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        // Нулевые байты
        $pos += 8;

        // Коэффициенты расчетной длины
        list(, $this->mu_XZ) = unpack("d", substr($data, $pos, 8));
        $pos += 8;
        list(, $this->mu_XY) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        // Гибкости
        list(, $this->flexCompressed) = unpack("d", substr($data, $pos, 8));
        $pos += 8;
        list(, $this->flexTensed) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        // Расстояние между точками раскрепления
        list(, $this->bucklingDistance) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        // Нулевые байты
        $pos += 16;

        // Расчетные длины
        list(, $this->length_XZ) = unpack("d", substr($data, $pos, 8));
        $pos += 8;
        list(, $this->length_XY) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        // Неизвестные значения
        list(, $this->noname1) = unpack("d", substr($data, $pos, 8));
        $pos += 8;
        list(, $this->noname2) = unpack("d", substr($data, $pos, 8));
        $pos += 8;

        // Нулевые байты
        $pos += 8;

        // Проверка перемещений от всех нагрузок (0 - да, 1 - нет)
        list(, $this->deflectionFromAllLoadsToBeChecked) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // Проверка перемещений от временных нагрузок (0 - да, 1 - нет)
        list(, $this->deflectionFromTemporaryLoadsToBeChecked) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // Нулевые байты
        $pos += 32;

        // Предельные относительные перемещения
        list(, $this->limitRelativeDisplacementFromAllLoads) = unpack("f", substr($data, $pos, 4));
        $pos += 4;
        list(, $this->limitRelativeDisplacementFromTemporaryLoads) = unpack("f", substr($data, $pos, 4));
        $pos += 4;

        // Нулевые байты
        $pos += 22;

        // Дополнительная группа (0 - нет, 1 - да)
        list(, $this->isGroupAdditional) = unpack("C", substr($data, $pos, 1));
        $pos += 1;

        // Нулевые байты
        $pos += 40;

        // Предельные абсолютные перемещения
        list(, $this->limitAbsoluteDisplacementFromAllLoads) = unpack("f", substr($data, $pos, 4));
        $pos += 4;
        list(, $this->limitAbsoluteDisplacementFromTemporaryLoads) = unpack("f", substr($data, $pos, 4));
        $pos += 4;
    }

    /*
     * Read single list block
     * 
     * @param string $data Binary data
     * @param int $pos Position of cursor in data
     */

    function readSingleListBlock($data, &$pos) {
        // Имя группы
        $this->name = $this->readCharArray($data, $pos);
        
        // Кол-во элементов в группе
        list(, $count) = unpack("I", substr($data, $pos, 4));
        $pos += 4;
        
        for ($i = 0; $i < $count; $i++) {
            list(, $this->list[]) = unpack("I", substr($data, $pos, 4));
            $pos += 4;
        }
    }

    /*
     * Get portion of string upto null byte (including null byte)
     * 
     * @param string $data Binary data
     * @param int $pos Position of cursor in data
     * 
     * @return string
     */

    private function readCharArray($data, &$pos) {
        // Ищем нулевой байт - символ конца имени
        $nullBytePos = strpos($data, "\x00", $pos);

        // Читаем 
        $s = substr($data, $pos, $nullBytePos - $pos + 1);

        // Изменяем курсор
        $pos = $nullBytePos + 1;

        return $s;
    }

}

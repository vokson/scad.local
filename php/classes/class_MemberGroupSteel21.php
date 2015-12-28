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

    function get_from_spr($data) {
        $pos = 0;

        //кол-во байт в типе стали (ищем первое вхождение нулевого байта)
        $steelTypeByteCount = strpos($data, "\x00");
            
        // класс стали
        $this->steel_type = substr($data, 0, $steelTypeByteCount);
        $pos += 80;

        // Тип группы
        $this->group_type = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // Тип элемента
        $this->member_type = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // Нулевые байты
        $pos += 3;

        // Коэффициент расчетной длины отличается от нормативных
        $this->isMuSameWithRegulation = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // 00 - расчетные длины, 01 - коэффициенты расчетной длины
        $this->isMuUsed = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // Неупругая работа (0 - нет, 1 - да)
        $this->isOnlyElastic = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // сопротивление стали
        $this->steel_Ry = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        //gamma_N
        $this->gamma_n = unpackDouble( substr($data, $pos, 8));
        $pos += 8;
        //gamma_C
        $this->gamma_c = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        // Нулевые байты
        $pos += 8;

        // Коэффициенты расчетной длины
        $this->mu_XZ = unpackDouble( substr($data, $pos, 8));
        $pos += 8;
        $this->mu_XY = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        // Гибкости
        $this->flexCompressed = unpackDouble( substr($data, $pos, 8));
        $pos += 8;
        $this->flexTensed = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        // Расстояние между точками раскрепления
        $this->bucklingDistance = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        // Нулевые байты
        $pos += 16;

        // Расчетные длины
        $this->length_XZ = unpackDouble( substr($data, $pos, 8));
        $pos += 8;
        $this->length_XY = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        // Неизвестные значения
        $this->noname1 = unpackDouble( substr($data, $pos, 8));
        $pos += 8;
        $this->noname2 = unpackDouble( substr($data, $pos, 8));
        $pos += 8;

        // Нулевые байты
        $pos += 8;

        // Проверка перемещений от всех нагрузок (0 - да, 1 - нет)
        $this->deflectionFromAllLoadsToBeChecked = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // Проверка перемещений от временных нагрузок (0 - да, 1 - нет)
        $this->deflectionFromTemporaryLoadsToBeChecked = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // Нулевые байты
        $pos += 32;

        // Предельные относительные перемещения
        $this->limitRelativeDisplacementFromAllLoads = unpackFloat( substr($data, $pos, 4));
        $pos += 4;
        $this->limitRelativeDisplacementFromTemporaryLoads = unpackFloat( substr($data, $pos, 4));
        $pos += 4;

        // Нулевые байты
        $pos += 22;

        // Дополнительная группа (0 - нет, 1 - да)
        $this->isGroupAdditional = unpackInt_1( substr($data, $pos, 1));
        $pos += 1;

        // Нулевые байты
        $pos += 40;

        // Предельные абсолютные перемещения
        $this->limitAbsoluteDisplacementFromAllLoads = unpackFloat( substr($data, $pos, 4));
        $pos += 4;
        $this->limitAbsoluteDisplacementFromTemporaryLoads = unpackFloat( substr($data, $pos, 4));
        $pos += 4;
    }

}

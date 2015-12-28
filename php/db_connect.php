<?php
define('host','localhost'); // имя хоста
define('user','root'); // заданное вами имя пользователя, либо определенное провайдером
define('pswd','vokson'); // заданный вами пароль

//Расчет анкерных болтов
define('SCAD','scad'); // имя базы данных, которую вы должны создать

define('translate_group','translate_group');  //таблица с переводами свойств для групп
define('document','document');  //таблица хранение документов SCAD
define('document_bin','document_bin');  //таблица хранение документов SCAD в бинарном виде
define('RSU','RSU');  // таблица строк РСУ
define('RSU_UNIF','RSU_UNIF');  // таблица строк РСУ-унификация
define('member_group','member_group');  // таблица групп элементов
define('member_group_for_steel','member_group_for_steel');  // таблица групп для подбора
define('nodes','nodes');  // таблица узлов
define('members','members');  // таблица элементов
define ('combinations', 'combinations'); //таблица комбинаций

//Номера документов
define('D_member',1); //Элементы
define('D_node',4); //Узлы
define('D_member_group',47); //Группы элементов
define('D_member_group_for_steel',28); //Группы для подбора стали
define ('D_combinations', 36); //Комбинации
?>
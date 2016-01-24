<?php

include_once './db_connect.php';
//подключаемся к MySQL
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");

//Создание базы данных SCAD
$res = mysql_query("CREATE DATABASE ".SCAD." CHARACTER SET utf8 COLLATE utf8_general_ci;");
if ($res == TRUE) echo "Database ".SCAD." was created.<br>"; else "Database wasn't created...<br>";
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");

//создаем таблицы
$res = mysql_query("SET NAMES utf8");

//таблица хранение неразобранных частей  документа
mysql_query("CREATE TABLE ".document." (
    id INT NOT NULL,
    text TEXT,
    PRIMARY KEY (id))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".document." was created.</b><br>";break;
    case 1050: echo "Table ".document." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица хранение неразобранных частей  документа
mysql_query("CREATE TABLE ".document_bin." (
    id INT NOT NULL AUTO_INCREMENT,
    number INT NOT NULL,
    byte_offset INT NOT NULL,
    byte_count INT NOT NULL,
    body MEDIUMBLOB,
    appendix MEDIUMBLOB,
    PRIMARY KEY (id))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".document_bin." was created.</b><br>";break;
    case 1050: echo "Table ".document_bin." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица перевода
mysql_query("CREATE TABLE ".translate_group." (
    id INT NOT NULL AUTO_INCREMENT,
    edit INT(1),
    english TEXT,
    russian TEXT,
    description TEXT,
    PRIMARY KEY (id))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".translate_group." was created.</b><br>";break;
    case 1050: echo "Table ".translate_group." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//Очищаем таблицу
mysql_query("TRUNCATE TABLE ".translate_group);
//Заполнение
$mas = array(
    array(0,'id','id','Номер группы'),
    array(1,'appointment','Назначение','1 - группа для побора арматуры, 2 - общего назначения'),
    array(1,'type','Тип группы эл-ов','1 - стержни, 2 - пластины (используется только для подбора арматуры)'),
    array(1,'name','Наименование','Имя группы'),
    array(1,'list','Список элементов','Номера элементов через пробел'),
    array(1,'steel_type','Класс стали',''),
    array(1,'steel_Ry','Ry, МПа',''),
    array(1,'gamma_c','Гамма_C',''),
    array(1,'flexibility','Предельная гибкость',''),
    array(1,'group_type','Тип группы подбора','0 - констр эл-т, 1 - группа эл-тов'),
    array(1,'mu_XZ','Мю_XZ','Коэффициент расчетной длины в пл-ти XoZ'),
    array(1,'mu_XY','Мю_XY','Коэффициент расчетной длины в пл-ти XoY')
);

foreach ($mas as $s)
  mysql_query("INSERT INTO ".translate_group." (edit,english,russian,description)
               VALUES ('".$s[0]."','".$s[1]."','".$s[2]."','".$s[3]."')");

//таблица РСУ
mysql_query("CREATE TABLE ".RSU." (
uin VARCHAR (40) NOT NULL,
element  INT NOT NULL ,
UNG SMALLINT NOT NULL ,
section SMALLINT NOT NULL ,
CT SMALLINT NOT NULL ,
criterion_number SMALLINT NOT NULL ,
criterion_value DOUBLE NOT NULL,
view SMALLINT NOT NULL ,

type VARCHAR(1) NOT NULL ,
seismic INT(1) ZEROFILL,
special INT(1) ZEROFILL,
impact INT(1) ZEROFILL,

N DOUBLE NULL,
Nx DOUBLE NULL,
Ny DOUBLE NULL,
Nz DOUBLE NULL,
M DOUBLE NULL,
Mx DOUBLE NULL,
My DOUBLE NULL,
Mxy DOUBLE NULL,
Mz DOUBLE NULL,
Mk DOUBLE NULL,
Q DOUBLE NULL,
Qx DOUBLE NULL,
Qy DOUBLE NULL,
Qz DOUBLE NULL,
Txy DOUBLE NULL,
Txz DOUBLE NULL,

formula  TEXT NOT NULL,
PRIMARY KEY (uin))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".RSU." was created.</b><br>";break;
    case 1050: echo "Table ".RSU." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица РСУ-Унификация
mysql_query("CREATE TABLE ".RSU_UNIF." (
uin VARCHAR (40) NOT NULL,
element  INT NOT NULL ,
UNG SMALLINT NOT NULL ,
section SMALLINT NOT NULL ,
CT SMALLINT NOT NULL ,
criterion_number SMALLINT NOT NULL ,
criterion_value DOUBLE NOT NULL,
view SMALLINT NOT NULL ,

type VARCHAR(1) NOT NULL ,
seismic INT(1) ZEROFILL,
special INT(1) ZEROFILL,
impact INT(1) ZEROFILL,

N DOUBLE NULL,
Nx DOUBLE NULL,
Ny DOUBLE NULL,
Nz DOUBLE NULL,
M DOUBLE NULL,
Mx DOUBLE NULL,
My DOUBLE NULL,
Mxy DOUBLE NULL,
Mz DOUBLE NULL,
Mk DOUBLE NULL,
Q DOUBLE NULL,
Qx DOUBLE NULL,
Qy DOUBLE NULL,
Qz DOUBLE NULL,
Txy DOUBLE NULL,
Txz DOUBLE NULL,

formula  TEXT NOT NULL,
PRIMARY KEY (uin))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".RSU_UNIF." was created.</b><br>";break;
    case 1050: echo "Table ".RSU_UNIF." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица c группами элементов
mysql_query("CREATE TABLE ".member_group." (
id INT NOT NULL AUTO_INCREMENT,
appointment  INT(1) NOT NULL DEFAULT '2',
type  INT(1) NOT NULL DEFAULT '1',
name VARCHAR (100) NOT NULL DEFAULT 'NONAME',
list TEXT NOT NULL,
PRIMARY KEY (id))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".member_group." was created.</b><br>";break;
    case 1050: echo "Table ".member_group." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица c группами для подбора
mysql_query("CREATE TABLE ".member_group_for_steel." (
id INT NOT NULL AUTO_INCREMENT,

steel VARCHAR(10),
Ry  FLOAT NOT NULL DEFAULT '0',

group_type  INT(1) NOT NULL DEFAULT '1',
member_type  INT(1) NOT NULL DEFAULT '1',
isMuReg INT(1) NOT NULL DEFAULT '1',
isMuUsed INT(1) NOT NULL DEFAULT '0',
onlyElastic INT(1) NOT NULL DEFAULT '1',
addGroup INT(1) NOT NULL DEFAULT '0',
check_DAL INT(1) NOT NULL DEFAULT '0',
check_DTL INT(1) NOT NULL DEFAULT '0',

gamma_n  FLOAT NOT NULL DEFAULT '1',
gamma_c  FLOAT NOT NULL DEFAULT '1',


FC  FLOAT NOT NULL DEFAULT '200',
aFC  FLOAT NOT NULL DEFAULT '0',
FT  FLOAT NOT NULL DEFAULT '400',
aFT  FLOAT NOT NULL DEFAULT '0',
BD  FLOAT NOT NULL DEFAULT '0',

mu_XZ  FLOAT NOT NULL DEFAULT '1',
mu_XY  FLOAT NOT NULL DEFAULT '1',
length_XZ  FLOAT NOT NULL DEFAULT '1',
length_XY  FLOAT NOT NULL DEFAULT '1',

noname1  FLOAT NOT NULL DEFAULT '1',
noname2  FLOAT NOT NULL DEFAULT '1',

limit_RDAL FLOAT NOT NULL DEFAULT '0.005',
limit_RDTL FLOAT NOT NULL DEFAULT '0.005',
limit_ADAL FLOAT NOT NULL DEFAULT '0.01',
limit_ADTL FLOAT NOT NULL DEFAULT '0.01',

name VARCHAR (100) NOT NULL DEFAULT 'NONAME',
list TEXT NOT NULL,
PRIMARY KEY (id))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".member_group_for_steel." was created.</b><br>";break;
    case 1050: echo "Table ".member_group_for_steel." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица c узлами
mysql_query("CREATE TABLE ".nodes." (
id INT NOT NULL AUTO_INCREMENT,
removed  INT(1) NOT NULL,
x  FLOAT NOT NULL,
y  FLOAT NOT NULL,
z  FLOAT NOT NULL,
PRIMARY KEY (id)) ENGINE=INNODB;");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".nodes." was created.</b><br>";break;
    case 1050: echo "Table ".nodes." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица c элементами
mysql_query("CREATE TABLE ".members." (
id INT NOT NULL AUTO_INCREMENT,
removed  INT(1) NOT NULL,
type  INT NOT NULL,
section  INT NOT NULL,
N1 INT NULL,
N2 INT NULL,
N3 INT NULL,
N4 INT NULL,
N5 INT NULL,
N6 INT NULL,
N7 INT NULL,
N8 INT NULL,
PRIMARY KEY (id),
FOREIGN KEY (N1) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N2) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N3) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N4) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N5) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N6) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N7) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT,
FOREIGN KEY (N8) REFERENCES ".nodes."(id)
          ON UPDATE CASCADE
          ON DELETE RESTRICT
) ENGINE=INNODB;");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".members." was created.</b><br>";break;
    case 1050: echo "Table ".members." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//таблица c комбинациями
mysql_query("CREATE TABLE ".combinations." (
id INT NOT NULL AUTO_INCREMENT,
comb INT NOT NULL,
koef  INT NOT NULL,
value FLOAT NOT NULL,
PRIMARY KEY (id))");

switch (mysql_errno()) {
    case 0: echo "<b>Table ".combinations." was created.</b><br>";break;
    case 1050: echo "Table ".combinations." already exist.<br>";break;
    default: echo mysql_errno() . ": " . mysql_error(). "<br>";break;
}

//закрываем соединение с базой
mysql_close($dbh);
?>
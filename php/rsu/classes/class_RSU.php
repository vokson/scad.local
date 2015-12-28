<?php
class RSU {

  public $element = NULL; //номер эл-та
  public $UNG = NULL; //УНГ
  public $section = NULL; //Сечение
  public $CT = NULL; //СТ
  public $criterion_number = NULL; //Номер критерия
  public $criterion_value = NULL; //Значение критерия
  public $view = NULL; //Вид
  public $type = NULL; //Тип
  public $seismic = NULL; //Сейсмика
  public $impact = NULL; //Крановая
  public $special = NULL; //Особая
  public $formula = NULL; //Формула

  public $forces_bar_names = array('N','Mk','M','Q','My','Qz','Mz','Qy');
//  public $forces_bar_names = array('Qy','Mz','Qz','My','Q','M','Mk','N');
  public $forces_bar_values = array();

  public $forces_plate_names = array('Nx','Ny','Nz','Txy','Txz','Mx','My','Mxy','Qx','Qy');
  public $forces_plate_values = array();
  
  public $factor_f = NULL;// гамма f
  public $long_part = NULL;// коэффициент длительной части
  
}
?>

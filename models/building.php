<?php
class Building extends AppModel {
  var $hasMany = array('Resident'=>array('dependent'=>true), 'Ban'=>array('dependent'=>true));
}
?>

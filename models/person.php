<?php
class Person extends AppModel {
  var $displayField = 'firstName';
  var $hasMany = array('Identification'=>array('dependent'=>true),'Ban'=>array('dependent'=>true), 'Entry'=>array('dependent'=>true));
}
?>

<?php
class Cardtype extends AppModel {
  var $display = 'name';
  var $hasMany = array('Identification');
}
?>

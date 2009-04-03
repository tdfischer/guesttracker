<?php
class Resident extends AppModel {
  var $belongsTo = array('Identification', 'Building');
  var $hasMany = 'Entry';
  var $recursive = 2;
}
?>

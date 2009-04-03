<?php
class Identification extends AppModel {
  var $hasOne = array('Resident');
  var $belongsTo = 'Person';
  var $displayField = 'card_num';
  var $recursive = 1;
}
?>

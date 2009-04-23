<?php
class Group extends AppModel {
  var $display = 'name';
  var $hasAndBelongsToMany = array('User');
  var $actsAs = array('Acl' => array('Aro','Aco'));

  function parentNode() {
    return 'groups';
  }
}
?>

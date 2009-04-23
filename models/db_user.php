<?php
App::import('Model', 'UserBase');

class DbUser extends UserBase {
    var $useTable = 'users';
    var $hasAndBelongsToMany = array('Group');
    var $recursive = 1;
}
?>

<?php
class Entry extends AppModel {
  var $belongsTo = array('Person', 'Resident');
  var $recursive = 3;
  var $validate = array(
    'resident_id' => array('rule'=>'numeric', 'required'=>true, 'allowEmpty' => false),
    'person_id' => array('rule'=>'numeric', 'required'=>true, 'allowEmpty' => false)
  );
}
?>

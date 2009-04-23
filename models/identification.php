<?php
class Identification extends AppModel {
    var $hasOne = array('Resident');
    var $belongsTo = array('Person', 'Cardtype');
    var $displayField = 'card_num';
    var $uses = array('Person');
    var $recursive = 1;
    var $validate = array('person_id'=>array('required'=>true, 'allowEmpty'=>false));
    
    public function validates($options) {
        if (isset($this->data['person_id'])) {
            $person = $this->data['person_id'];
            if ($this->Person->findById($person)==null)
            $this->invalidate('person_id');
        } else {
            $this->invalidate('person_id');
        }
        return count($this->invalidFields()) == 0;
    }
}
?>

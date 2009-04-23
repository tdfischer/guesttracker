<?php
class IdentificationsController extends AppController {
  var $scaffold;
  var $components = array('RequestHandler', 'Auth', 'Acl');
  var $helpers = array('Ajax', 'Javascript', 'Form');

  public function view($id) {
    $this->set('card', $this->Identification->findByCardNum($id));
  }

  public function search() {
    $this->set('card', $this->Identification->findByCardNum($this->data['Identification']['card_num']));
  }

  public function add() {
    $this->Identification->save($this->data);
  }
}
?>

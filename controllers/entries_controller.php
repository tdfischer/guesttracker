<?php
class EntriesController extends AppController {
  var $scaffold;
  var $helpers = array('Ajax', 'Javascript', 'Widgets');
  var $components = array('Session');
  var $uses = array('Entry', 'Person', 'Resident', 'Identification');
  
  public function add() {
    $resident = $this->Identification->findByCardNum($this->data['Resident']['card_num']);
    $guest = $this->Identification->findByCardNum($this->data['Guest']['card_num']);
    $this->data['Entry']['person_id'] = $guest['Person']['id'];
    $this->data['Entry']['resident_id'] = $resident['Resident']['id'];
    if ($this->Entry->save($this->data))
      $this->Session->setFlash('Checked in!');
    else
      $this->Session->setFlash('Failed to check in.');
    $this->redirect("/entries");
  }
  
  public function index() {
    $this->set('guests', $this->Entry->find('all',array('limit'=>10, 'order'=>'created')));
  }
}
?>

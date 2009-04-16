<?php
class EntriesController extends AppController {
  var $scaffold;
  var $helpers = array('Ajax', 'Javascript', 'Widgets');
  var $components = array('Session');
  var $uses = array('Entry', 'Person', 'Resident', 'Identification');
  
  public function create() {
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
  
  public function search() {
    $this->set('ResidentById', $this->Resident->findAll(array('Identification.card_num'=>$this->data['Resident']['card_num'])));
    
    $residents = $this->Person->findAll(array('Person.firstName'=>$this->data['Resident']['name'],'Person.lastName'=>$this->data['Resident']['name']));
    
    $residentMatches = array();
    foreach($residents as $resident) {
      $residentMatches = array_merge($residentMatches, $this->Resident->findAll(array('Identification.firstName' => $this->data['Resident']['name'], 'Identification.lastName' => $this->data['Resident']['name'], 'Identification.person_id' => $resident['Person']['id'])));
    }
    $this->set('Residents', $residentMatches);
    $this->set('RoomResidents', $this->Resident->findAllByRoom($this->data['Resident']['room']));
    $this->set('GuestById', $this->Identification->findAll(array('card_num'=>$this->data['Guest']['card_num'])));
    $this->set('Guests', $this->Person->findAll(array('firstName'=>$this->data['Guest']['name'], 'lastName'=>$this->data['Guest']['name'])));
  }
  
  public function index() {
    $this->set('guests', $this->Entry->find('all',array('limit'=>10, 'order'=>'created')));
  }
}
?>

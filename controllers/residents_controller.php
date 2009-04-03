<?php
class ResidentsController extends AppController {
  var $name = 'Residents';
  var $scaffold;

  public function index() {
    $this->set('residents', $this->Resident->find('all'));
  }
}
?>

<?php
class PeopleController extends AppController {
  var $scaffold;

  public function add() {
    $this->Person->save($this->data);
  }

  public function search() {
    if (isset($this->data['Person']['name']))
      list($this->data['Person']['firstName'], $this->data['Person']['lastName']) = explode(' ', $this->data['Person']['name'].' ', 2);
    $firstName = $this->data['Person']['firstName'];
    $lastName = $this->data['Person']['lastName'];
    $this->set('people', $this->Person->find('all', array('conditions' => array( 'Person.firstName LIKE' => $firstName.'%', 'Person.lastName LIKE' => $lastName.'%'))));
  }
}
?>

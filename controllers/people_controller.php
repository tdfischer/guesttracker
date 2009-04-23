<?php
/**
 * Copyright (C) 2009 by Trever Fischer
 * wm161@wm161.net
 *
 * This file is part of GuestTracker.
 * 
 * GuestTracker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GuestTracker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with GuestTracker. If not, see <http://www.gnu.org/licenses/>
 */

class PeopleController extends AppController {
  var $scaffold;
  var $paginate = array(
      'limit' => 10,
      'order' => array(
        'Entry.id' => 'desc'
      )
      );

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

  public function view($id) {
    $this->set('Person', $this->Person->findById($id));
    $this->set('data', $this->paginate('Entry', array('Person.id LIKE' => $id.'%')));
  }
}
?>

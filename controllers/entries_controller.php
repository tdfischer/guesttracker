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

class EntriesController extends AppController {
  var $scaffold;
  var $helpers = array('Ajax', 'Javascript', 'Widgets');
  var $components = array('Session', 'Auth', 'Acl');
  var $uses = array('Entry', 'Ban', 'Identification');
  
  public function create() {
    $resident = $this->Identification->findByCardNum($this->data['Resident']['card_num']);
    //debug($resident);
    if (!empty($resident['Resident'])) {
        $valid = array();
        $invalid = array();
        $old = array();
        $this->data['Entry']['resident_id'] = $resident['Resident']['id'];
        foreach($this->data['Guest'] as $guest) {
            $guest = $this->Identification->findByCardNum($guest['card_num']);
            $oldentry = $this->Entry->find(array('person_id'=>$guest['Person']['id'], 'active'=>'1'));
            if ($oldentry) {
                $old[] = $guest;
            } elseif ($guest) {
                ///TODO: Search bans by building_id
                //$this->Ban->find(array('person_id' => $guest['Person']['id'], 'building_id'=>));
                $this->data['Entry']['person_id'] = $guest['Person']['id'];
                $this->Entry->save($this->data);
                $valid[] = $guest;
            } else {
                $invalid[] = $guest['card_num'];
            }
        }
        if (!empty($old))
            $this->Session->setFlash(__('Guest(s) already checked in elsewhere', true), 'checkin-old', array('old'=>$old), 'checkin-old');
        if (!empty($valid))
            $this->Session->setFlash(__('Successfully checked in.', true), 'checkin-valid', array('valid'=>$valid), 'checkin-valid');
        if (!empty($invalid))
            $this->Session->setFlash(__('Invalid card(s).', true), 'checkin-invalid', array('invalid'=>$invalid), 'checkin-invalid');
    } else {
        if (empty($resident['Person']))
            $this->Session->setFlash(__("Resident ID not found", true));
        else
            ///FIXME: Translatable
            $this->Session->setFlash($resident['Person']['firstName'].' '.$resident['Person']['lastName'].' is not a resident.');
    }
    //debug($this->data['Guest']);
    $this->redirect("/");
    /*$resident = $this->Identification->findByCardNum($this->data['Resident']['card_num']);
    $guest = $this->Identification->findByCardNum($this->data['Guest']['card_num']);
    $this->data['Entry']['person_id'] = $guest['Person']['id'];
    $this->data['Entry']['resident_id'] = $resident['Resident']['id'];
    if ($this->Entry->save($this->data))
      $this->Session->setFlash('Checked in!');
    else
      $this->Session->setFlash('Failed to check in.');
    $this->redirect("/");*/
  }
  
  public function search() {
    $search = array();

    $resident = array();
    if (!empty($this->data['Resident']['name'])) {
        $name = explode(' ', $this->data['Resident']['name'],2);
        if (count($name)==2) {
        $firstName = '%'.$name[0].'%';
        if (count($name) == 1)
            $name[1] = $name[0];
        $lastName = '%'.$name[1].'%';
        $resident = $this->Identification->find(array('Person.firstName LIKE'=>$firstName, 'Person.lastName LIKE'=>$lastName));
        } else {
            $resident = $this->Identification->find(array('or'=>array('Person.firstName LIKE'=>'%'.$this->data['Resident']['name'].'%', 'Person.lastName LIKE'=>'%'.$this->data['Resident']['name'].'%')));
        }
    }

    $guest = array();
    if (!empty($this->data['Guest']['name'])) {
        $name = explode(' ', $this->data['Guest']['name'], 2);
        if (count($name)==2) {
            $firstName = '%'.$name[0].'%';
            if (count($name) == 1)
                $name[1] = $name[0];
            $firstName = '%'.$name[1].'%';
            $guest = $this->Identification->find(array('Person.firstName LIKE'=>$firstName, 'Person.lastName LIKE'=>$firstName));
        } else {
            $guest = $this->Identification->find(array('or' => array('Person.firstName LIKE'=>'%'.$this->data['Guest']['name'].'%', 'Person.lastName LIKE'=>'%'.$this->data['Guest']['name'].'%')));
        }
    }
    
    if (!empty($resident))
        $search[] = array('Entry.resident_id' => $resident['Resident']['id']);
    if (!empty($guest))
        $search[] = array('Entry.person_id' => $guest['Person']['id']);

    $resident = $this->Identification->find(array('Identification.card_num' => $this->data['Resident']['card_num']));
    $guest = $this->Identification->find(array('Identification.card_num' => $this->data['Guest']['card_num']));
    
    if (!empty($guest))
        $search[] = array('Entry.person_id' => $guest['Person']['id']);
    if (!empty($resident))
        $search[] = array('Entry.resident_id' => $resident['Resident']['id']);


    if (!empty($this->data['Resident']['room']))
        $search[] = array('Resident.room' => $this->data['Resident']['room']);

    debug($search);
    if (empty($search))
        $this->set('results', array());
    else
        $this->set('results', $this->Entry->findAll($search));
  }
  
  public function index() {
    $this->set('guests', $this->Entry->find('all',array('limit'=>10, 'order'=>'created')));
  }
}
?>

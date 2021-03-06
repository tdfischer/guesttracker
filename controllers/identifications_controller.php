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

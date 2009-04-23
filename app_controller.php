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

class AppController extends Controller {
  var $uses = array('User');
  var $helpers = array('Javascript', 'Ajax', 'Html', 'Widgets');
  var $components = array('Auth', 'Acl');
  function constructClasses() {
    $userClass=Configure::read('userBackendModel');
    ClassRegistry::init(array('class'=>$userClass, 'alias' => 'User', 'type'=>'Model'));
    Controller::constructClasses();
  }

  function beforeRender() {
    $this->set('user', $this->Auth->user());
    Controller::beforeRender();
  }

  function isAuthorized() {
    $user = $this->Auth->user();
    $aco = 'ROOT/controllers/'.$this->Auth->action();
    if ($user) {
        $user = $this->User->findById($user['User']['id']);
        foreach($user['Group'] as $group) {
            if ($this->Acl->check(array('model'=>'Group', 'foreign_key'=>$group['id']), $aco)) {
                $this->Auth->allow();
                return true;
            }
        }
    }
    return false;
  }

  function beforeFilter() {
    if (isset($this->Auth)) {
      $this->Auth->loginAction = '/users/login';
      $this->Auth->loginRedirect = '/dashboard';
      $this->Auth->authorize = 'controller';
    }
  }
}
?>

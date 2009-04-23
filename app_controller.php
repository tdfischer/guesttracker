<?php
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

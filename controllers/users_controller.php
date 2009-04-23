<?php
class UsersController extends AppController {
    var $scaffold;
    var $components = array('Auth', 'Acl');
    
    function beforeFilter() {
      AppController::beforeFilter();
      $this->Auth->allow('login');
    }

    function login() {

    }

    function logout() {
        $this->redirect($this->Auth->logout());
    }
}
?>

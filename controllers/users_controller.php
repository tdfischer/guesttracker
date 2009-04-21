<?php
class UsersController extends AppController {
    var $scaffold;
    var $components = array('Auth');

    function login() {

    }

    function logout() {
        $this->redirect($this->Auth->logout());
    }
}
?>

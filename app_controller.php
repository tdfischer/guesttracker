<?php
class AppController extends Controller {
  var $helpers = array('Javascript', 'Ajax', 'Html', 'Widgets');
  var $components = array('Auth');
  function constructClasses() {
    $userClass=Configure::read('userBackendModel');
    ClassRegistry::map('User', $userClass);
    Controller::constructClasses();
  }

  function beforeRender() {
    $this->set('user', $this->Auth->user());
    Controller::beforeRender();
  }
}
?>

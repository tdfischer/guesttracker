<?php
class AppController extends Controller {
  var $helpers = array('Javascript', 'Ajax', 'Html', 'Widgets');
  function constructClasses() {
    $userClass=Configure::read('userBackendModel');
    ClassRegistry::map('User', $userClass);
    Controller::constructClasses();
  }
}
?>

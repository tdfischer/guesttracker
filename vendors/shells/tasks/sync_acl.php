<?php
App::import('Core', 'Controller');
App::import('Component', 'Acl');

class SyncAclTask extends Shell {
  var $filter = array();
  
  function startup() {
    $this->Acl = new AclComponent();
    $controller = null;
    $this->Acl->startup($controller);
    $this->filter['controller'] = array('App');
    $this->filter['methods'] = get_class_methods('Controller');
  }

  function execute() {
    $controllers = Configure::listObjects('controller');
    $controllerRoot = $this->Acl->Aco->node('ROOT/controllers');
    if ($controllerRoot === false) {
      $this->error(__("Can't find controller root", true), __("\tCouldn't find the ACO 'ROOT/controllers'."));
    }
    $controllerRoot = $controllerRoot[0]['Aco']['id'];

    foreach($controllers as $controller) {
      if (in_array($controller, $this->filter['controller'])) continue;

      $controllerTree = $this->Acl->Aco->node('ROOT/controllers/'.$controller);
      if ($controllerTree === false) {
        $data = array('alias' => $controller, 'parent_id' => $controllerRoot);
	$this->Acl->Aco->create();
	$this->Acl->Aco->save($data);
	$controllerTree = $this->Acl->Aco->node('ROOT/controllers/'.$controller);
	if ($controllerTree === false) {
          $this->error(__("Error in ACO creation", true), sprintf(__("There was a problem while trying to create the ACO for the %s controller", true), $controller));
	} else {
          $this->out(__("Created ACO for ${controller}", true));
	}
      }
      $this->out(sprintf(__("Importing controller %s", true), $controller));
      App::import('Controller', $controller);
      foreach($this->_getMethods($controller.'Controller', 'methods') as $method) {
        $aco = $this->Acl->Aco->node('ROOT/controllers/'.$controller.'/'.$method);
        if (!$aco) {
          $data = array('parent_id' => $controllerTree[0]['Aco']['id'], 'alias'=>$method);
          $this->Acl->Aco->create();
          $this->Acl->Aco->save($data);
          $this->out(sprintf(__("Created ACO for %s::%s()", true), $controller, $method));
        }
      }
    }

    $this->out(__("ACO Tree syncronized!", true));
  }

  ///TODO: Update to use PHP5's introspection API
  function _getMethods($className, $filter = 'methods') {
    $c_methods = get_class_methods($className);
    $c_methods = array_diff($c_methods, $this->filter[$filter]);
    $c_methods = array_filter($c_methods, array($this,"_removePrivate"));

    return $c_methods;
  }

  function _removePrivate($var) {
    if (substr($var, 0, 1) == '_') return false;
    return true;
  }
}
?>

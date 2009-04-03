<?php
class WidgetsHelper extends AppHelper {
  var $helpers = array('Form', 'Javascript');
  function cardInput($name, $options = array()) {
    if (!isset($options['id']))
      $options['id'] = Inflector::camelize(str_replace('.', '_', $name));
    $script = "Event.observe(document, 'dom:loaded', function() {new Finder.Card('{$options['id']}');});";
    return $this->output($this->Form->input($name, $options)."\n".$this->Javascript->codeBlock($script));
  }

  function personFinder($name, $options = array()) {
    if (!isset($options['id']))
      $options['id'] = Inflector::camelize(str_replace('.', '_', $name));
    $script = "Event.observe(document, 'dom:loaded', function() {new Finder.Person('{$options['id']}');});";
    return $this->output($this->Form->input($name, $options)."\n".$this->Javascript->codeBlock($script));
  }
}
?>

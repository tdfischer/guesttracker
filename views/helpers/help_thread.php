<?php
class HelpThreadHelper extends AppHelper {
  function makeThread($thread, $path = array()) {
    $out = '';
    $out.= '<ul>';
    foreach($thread as $child) {
      $out .= "<li><a href='/help/view/{$child['HelpPage']['id']}'>{$child['HelpPage']['title']}</a>";
      $dive = false;
      foreach($path as $pathNode) {
        if ($pathNode['HelpPage']['id'] == $child['HelpPage']['id'])
	  $dive = true;
      }
      if ($dive) {
        $out .= $this->makeThread($child['children'], $path);
      }
      $out .= '</li>';
    }
    $out .= '</ul>';
    return $out;
  }
}
?>

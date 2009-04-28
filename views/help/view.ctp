<?php
print '<div class="sidebar">'.$helpThread->makeThread($tableOfContents, $path).'</div>';
$html->addCrumb("Help");
foreach($path as $node) {
  $html->addCrumb($node['HelpPage']['title'], "/help/view/".$node['HelpPage']['id']);
}
print "<h2>{$page['HelpPage']['title']}</h2>";
print "<div id='help-content'>{$page['HelpPage']['content']}</div>";


if (!empty($neighbors['prev']))
  print "<a href='/help/view/{$neighbors['prev']['HelpPage']['id']}'>&laquo; {$neighbors['pref']['HelpPage']['title']}</a>";
if (!empty($neighbors['next']))
  print "<a href='/help/view/{$neighbors['next']['HelpPage']['id']}'>{$neighbors['next']['HelpPage']['title']} &raquo;</a>";
?>

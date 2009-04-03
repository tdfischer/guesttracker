<?php
header("Pragma: no-cache");
header("Cache-control: no-store, no-cache, max-age=0, must-revalidate");
header("Content-type: text/x-json");
header("X-JSON: ".$content_for_layout);

echo $content_for_layout;
?>

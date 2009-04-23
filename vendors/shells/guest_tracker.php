<?php
class GuestTrackerShell extends Shell {
  var $tasks = array('SyncAcl');

  function main() {
    $this->out('GuestTracker Shell');
    $this->hr();
    $this->out('Commands:');
    $this->out('sync_acl');
    $this->out("\tSyncronizes the ACL list with controllers and methods");
    $this->hr();
  }
}
?>

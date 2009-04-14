<?php
class DashboardController extends AppController {
    var $uses = array('Entry', 'Resident');
    public function index() {
        $this->set('residents', $this->Resident->findAll());
        $this->set('guests', $this->Entry->findAll());
    }
}
?>
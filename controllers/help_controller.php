<?php
class HelpController extends AppController {
    var $scaffold;
    var $helpers = array('Html', 'HelpThread');
    var $components = array('Session');
    var $uses = array('HelpPage');

    function admin_delete($id) {
    	$this->HelpPage->id = $id;
	$page = $this->HelpPage->find();
	if ($this->HelpPage->delete()) {
	    $this->Session->setFlash("Page deleted.");
	} else {
	    $this->Session->setFlash("Could not delete page.");
	}
	$this->redirect("/help/view/{$page['HelpPage']['parent_id']}");
    }

    function admin_create() {
	$this->set('pageList', $this->HelpPage->generatetreelist(null, null, null, '- '));
	$this->render('admin_edit');
    }

    function admin_edit($id) {
        if (!empty($this->data)) {
	    $this->HelpPage->id = $id;
	    if ($this->HelpPage->save($this->data)) {
                $this->Session->setFlash("Page updated.");
	    }
	}
        $this->data = $this->HelpPage->findById($id);
	$this->set('pageList', $this->HelpPage->generatetreelist(null, null, null, '- '));
    }

    function view($id = 1) {
        $toc = $this->HelpPage->find('threaded');
	$this->set('tableOfContents', $toc);
	$page = $this->HelpPage->findById($id);
	$this->set('page', $page);
	$path = $this->HelpPage->getpath($id);
	$this->set('path', $path);
	$neighbors = $this->HelpPage->find('neighbors', array('id' => $id));
	$this->set('neighbors', $neighbors);
    }
}
?>

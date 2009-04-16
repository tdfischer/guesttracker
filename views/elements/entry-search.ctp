<h2>Search Entries</h2>
<?php echo $form->create('Entry', array('url'=>'/entries/search'));?>
<?php echo $widgets->cardInput('Resident.card_num', array('label'=>'Resident ID'));?>
<?php echo $widgets->cardInput('Guest.card_num', array('label'=>'Guest ID'));?>
<?php echo $widgets->personFinder('Resident.name', array('label'=>'Resident Name'));?>
<?php echo $widgets->personFinder('Guest.name', array('label'=>'Guest Name'));?>
<?php echo $form->text('Resident.room', array('label'=>'Room #'));?>
<?php echo $form->submit('Search');?>
<?php echo $form->end();?>
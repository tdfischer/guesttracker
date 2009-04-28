<h2>Search Entries</h2>
<?php echo $form->create('Entry', array('url'=>'/entries/search'));?>
<?php echo $form->input('Resident.card_num', array('label'=>'Resident ID'));?>
<?php echo $form->input('Guest.card_num', array('label'=>'Guest ID'));?>
<?php echo $form->input('Resident.name', array('label'=>'Resident Name'));?>
<?php echo $form->input('Guest.name', array('label'=>'Guest Name'));?>
<?php echo $form->input('Resident.room', array('label'=>'Room'));?>
<?php echo $form->input('Search.showInactive', array('label'=>'Show inactive entries', 'type'=>'checkbox'));?>
<?php echo $form->submit('Search');?>
<?php echo $form->end();?>
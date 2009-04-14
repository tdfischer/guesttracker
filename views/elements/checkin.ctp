<h2>Checkin</h2>
<?php echo $form->create('Entry', array('action'=>'/entries/create'));?>
<?php echo $widgets->cardInput('Resident.card_num',array('label'=>'Resident'));?>
<?php echo $widgets->cardInput('Guest.card_num',array('label'=>'Guest'));?>
<input type="submit" value="Ok"/>
<?php echo $form->end();?>
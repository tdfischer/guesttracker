<?php echo $form->create();?>
<?php echo $form->input('Identification.type');?>
<?php echo $form->input('Identification.card_num');?>
<?php echo $widgets->personFinder('Person.name');?>
<?php echo $form->end();?>

<?php echo $form->create();?>
<?php echo $form->input('HelpPage.title');?>
<?php echo $form->input('HelpPage.content');?>
<?php echo $form->input('HelpPage.parent_id', array('options' => $pageList));?>
<?php echo $form->end('Save');?>

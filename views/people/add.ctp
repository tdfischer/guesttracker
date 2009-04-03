<?php
echo $form->create();
echo $form->input("Person.lastName", array('label'=>'Last Name'));
echo $form->input("Person.firstName", array('label'=>'First Name'));
echo $form->end();
?>

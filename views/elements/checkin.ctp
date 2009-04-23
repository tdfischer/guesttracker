<?php echo $javascript->link('checkin-card-scanner.js', false);?>
<h2>Sign In</h2>
<?php echo $form->create('Entry', array('action'=>'create'));?>
<div class="sign-in">
    <div class="card-scanner">
        <?php echo $form->input('Resident.card_num', array('label'=>'Resident', 'id'=>'resident_card_num'));?>
        <?php echo $form->input('Guest.0.card_num', array('label'=>'Guest', 'id'=>'guest_card_num'));?>
        <?php echo $form->submit('Ok');?>
    </div>
</div>
<?php echo $form->end();?>
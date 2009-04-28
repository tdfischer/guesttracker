<?php
class Message extends AppModel {
    var $belongsTo = array('Recipient'=>array('className'=>'User', 'foreignKey'=>'to'),'Sender'=>array('className'=>'User', 'foreignKey'=>'from'));
}
?>

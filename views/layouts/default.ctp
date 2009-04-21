<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php echo $html->charset(); ?>
<title><?php echo $title_for_layout;?></title>
<?php
echo $html->meta('icon');
echo $html->css('main');
echo $javascript->link('prototype');
echo $javascript->link('scriptaculous');
echo $javascript->link('widgets');
//$javascript->link('signin', false);
echo $scripts_for_layout;
?>
</head>
<body>
<div id="wrapper">
    <div id="header">
        <h1>GuestTracker</h1>
<!--        <div id="epicenter">
            <div id="username">Fischer, Trever</div>
            <select id="locationPicker" name="location">
            <option>Exchange St. Residence Hall</option>
            <option>Administration</option>
            </select>
            <a href="#" id="logout">Logout</a><br />
            <a href="#" id="inbox">Inbox: 0</a>
        </div>-->
	<?php echo $this->element('epicenter');?>
	<br style="clear:both;"/>
    </div>
    <div id="content">
        <h2><?php echo $title_for_layout;?></h2>
        <div id="messages"><?php $session->flash();?></div>
        <?php echo $content_for_layout;?>
	<br style="clear:both;"/>
    </div>
</div>
</body>
</html>

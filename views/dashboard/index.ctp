<?php echo $this->element('checkin');?>

<?php echo $this->element('entry-search');?>

<h2>Popular Residents</h2>
<table>
<tr><th>Name</th><th>Room</th><th>Guests</th></tr>
<?php foreach($residents as $resident):?>
<tr>
<td><?php echo $resident['Identification']['Person']['lastName'].', '.$resident['Identification']['Person']['firstName'];?></td>
<td><?php echo $resident['Resident']['room'];?></td>
<td><?php echo count($resident['Entry']);?></td>
</tr>
<?php endforeach;?>
</table>

<h2>Old Guests</h2>
<table>
<tr><th>Name</th><th>Resident</th></tr>
<?php foreach($guests as $guest):?>
<tr>
<td><?php echo $guest['Person']['lastName'].', '.$guest['Person']['firstName'];?></td>
<td><?php echo $guest['Resident']['Identification']['Person']['lastName'].', '.$guest['Resident']['Identification']['Person']['firstName'];?></td>
</tr>
<?php endforeach;?>
</table>

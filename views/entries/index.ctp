<?php echo $this->element('checkin');?>

<h2>Old Guests</h2>
<table>
<tr><th>Name</th><th>Resident</th><th>Check-in</th></tr>
<?php foreach($guests as $guest):?>
<tr>
<td><a href="/people/view/<?php echo $guest['Person']['id'];?>"><?php echo $guest['Person']['lastName'].', '.$guest['Person']['firstName']?></a></td>
<td><a href="/residents/view/<?php echo $guest['Resident']['id'];?>"><?php echo $guest['Resident']['Identification']['Person']['lastName'].', '.$guest['Resident']['Identification']['Person']['firstName'];?></a></td>
<td><?php echo $guest['Entry']['created'];?></td>
</tr>
<?php endforeach;?>
</table>

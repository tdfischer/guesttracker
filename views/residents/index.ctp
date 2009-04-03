<h1>Residents</h1>
<table>
<tr><th>Name</th><th>Student ID</th><th>Room</th><th>Guests</th></tr>
<?php foreach($residents as $resident):?>
<tr>
<td><a href="/residents/view/<?php echo $resident['Resident']['id'];?>"><?php echo $resident['Identification']['Person']['lastName'].', '.$resident['Identification']['Person']['firstName'];?></a></td>
<td><a href="/identifications/view/<?php echo $resident['Identification']['id'];?>"><?php echo $resident['Identification']['cardNum'];?></a></td>
<td><a href="/buildings/view/<?php echo $resident['Building']['id'].'/'.$resident['Resident']['room'];?>"><?php echo $resident['Building']['name'].', Room '.$resident['Resident']['room'];?></td>
<td><a href="/residents/guests/<?php echo $resident['Resident']['id'];?>"><?php echo count($resident['Person']);?></a></td>
<?php endforeach;?>
</table>

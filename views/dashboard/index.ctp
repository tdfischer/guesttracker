<h1>Dashboard</h1>
<div id="signin">
<h2>Sign In</h2>
<label>Resident: <input type="text" name="resident"/></label>
<label>Guest: <input type="text" name="guests[]"/></label>
<p><input type="submit" value="Ok"/></p>
</div>

<h2>Popular Residents</h2>
<table>
<tr><th>Name</th><th>Room</th><th>Guests</th></tr>
<?php foreach($residents as $resident):?>
<tr>
<td><?php echo $resident['Identification']['Person']['lastName'].', '.$resident['Identification']['Person']['firstName'];?></td>
<td><?php echo $resident['Resident']['room'];?></td>
<td><?php echo count($resident['Person']);?></td>
</tr>
<?php endforeach;?>
</table>

<h2>Old Guests</h2>
<?php print_r($guests);?>
<table>
<tr><th>Name</th><th>Resident</th></tr>
<?php foreach($guests as $guest):?>
<tr>
<td><?php echo $guest['Person']['lastName'].', '.$guest['Person']['firstName'];?></td>
<td><?php echo $guest['Resident']['Identification']['Person']['lastName'].', '.$guest['Resident']['Identification']['Person']['firstName'];?></td>
</tr>
<?php endforeach;?>
</table>

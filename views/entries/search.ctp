<table>
<tr><th>Guest</th><th>Resident</th><th>Building</th><th>Room</th><th>Active</th><th colspan="2">Actions</th></tr>
<?php
foreach($results as $entry) {
    echo '<tr>';
    echo '<td><a href="/people/view/'.$entry['Person']['id'].'">'.$entry['Person']['lastName'].', '.$entry['Person']['firstName'].'</a></td>';
    echo '<td><a href="/residents/view/'.$entry['Resident']['id'].'">'.$entry['Resident']['Identification']['Person']['lastName'].', '.$entry['Resident']['Identification']['Person']['firstName'].'</a></td>';
    echo '<td><a href="/buildings/view/'.$entry['Resident']['Building']['id'].'">'.$entry['Resident']['Building']['name'].'</a></td>';
    echo '<td><a href="/buildings/room/'.$entry['Resident']['room'].'">'.$entry['Resident']['room'].'</a></td>';
    echo '<td>'. (($entry['Entry']['active'] == 1) ? 'Yes' : 'No').'</td>';
    echo '<td><a href="/entries/view/'.$entry['Entry']['id'].'">View</a></td>';
    echo '<td><a href="/entries/checkout/'.$entry['Entry']['id'].'">Check out</a></td>';
    echo '</tr>';
}
?>
</table>

<table>
<?php
echo $html->tableHeaders(array('Guest', 'Resident', 'Building', 'Room', ''));
foreach($results as $entry) {
    echo '<tr>';
    echo '<td><a href="/people/view/'.$entry['Person']['id'].'">'.$entry['Person']['lastName'].', '.$entry['Person']['firstName'].'</a></td>';
    echo '<td><a href="/residents/view/'.$entry['Resident']['id'].'">'.$entry['Resident']['Identification']['Person']['lastName'].', '.$entry['Resident']['Identification']['Person']['firstName'].'</a></td>';
    echo '<td><a href="/buildings/view/'.$entry['Resident']['Building']['id'].'">'.$entry['Resident']['Building']['name'].'</a></td>';
    echo '<td><a href="/buildings/room/'.$entry['Resident']['room'].'">'.$entry['Resident']['room'].'</a></td>';
    echo '<td><a href="/entries/view/'.$entry['Entry']['id'].'">view</a></td>';
    echo '</tr>';
}
?>
</table>
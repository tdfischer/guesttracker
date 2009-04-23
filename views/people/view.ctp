<?php
 echo "<h2>" . $Person['Person']['lastName'] . ", " . $Person['Person']['firstName'] . "</h2>";
 foreach ($Person['Identification'] as $id) {
   echo "<div>" . $id['type'] . " : <a href=\"/identificaton/view/" . $id['id'] . "\">" . $id['card_num'] . "</a></div>";
 }

 //Bans
 if (sizeof($Person['Ban']) > 0) {

   // # Ban(s)
   echo "<h3>" . sizeof($Person['Ban']) . " Ban";
   if (sizeof($Person['Ban']) > 1) { echo "s"; }
   echo "</h3>";

   echo "<table><th>Building</th><th>Reason</th>";
   foreach ($Person['Ban'] as $ban) {
     echo "<tr><td<a href=\"/buildings/view/" . $ban['Building']['id'] . "\">" . $ban['Building']['name'] . "</a></td>";
     echo "<td>" . $ban['notes'] . "</td>";
     echo "</table>";
   }
 } else {
   echo "<h3>No Bans</h3>";
 }

 echo "<h3>Entries</h3>";
 if (sizeof($data) > 0) {
   echo "<table>";
  // echo "<table><tr><th>" . $paginator->sort('ID', 'id') . "</th>";
  // echo "<th>" . $paginator->sort('Resident Last Name', 'lastname') . "</th>";
  // echo "<th>" . $paginator->sort('Resident First Name', 'firstname') . "</th>";
   echo "<th>ID</th><th>Resident Last Name</th><th>Resident First Name</th>";
   foreach ($data as $entry) {
     echo "<tr><td><a href=\"/entries/view/" . $entry['Entry']['id'] . "\">" . $entry['Entry']['id'] . "</a></td>";
     echo "<td><a href=\"/person/view/" . $entry['Resident']['Identification']['Person']['id'] . "\">" . $entry['Resident']['Identification']['Person']['lastName'] . "</a></td>";
     echo "<td><a href=\"/person/view/" . $entry['Resident']['Identification']['Person']['id'] . "\">" . $entry['Resident']['Identification']['Person']['firstName'] . "</a></td></tr>";
   }
   echo "</table>";
 } else {
   echo "<h5>None</h5>";
 }

?>

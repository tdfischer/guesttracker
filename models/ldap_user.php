<?php
class LdapUser extends AppModel {
    var $userDbConfig = 'ldap';
    var $primaryKey = 'uid';
    var $defaultObjectClass = 'inetOrgPerson';
}
?>

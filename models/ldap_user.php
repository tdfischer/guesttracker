<?php
App::import('Model', 'UserBase');

class LdapUser extends UserBase {
    var $userDbConfig = 'ldap';
    var $primaryKey = 'uid';
    var $defaultObjectClass = 'inetOrgPerson';
}
?>

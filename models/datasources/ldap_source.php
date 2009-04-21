<?php
/**
 * LdapSource
 * @author euphrate_ylb (base class + "R" in CRUD)
 * @author gservat (aka znoG) ("C", "U", "D" in CRUD)
 * @date 07/2007 (updated 04/2008)
 * @license GPL
 */
class LdapSource extends DataSource {
    var $description = "Ldap Data Source";
    
    var $cacheSources = true;
    
    var $_baseConfig = array (
        'host' => 'localhost',
        'port' => 389,
        'version' => 3
    );
    
    var $__descriptions = array();
    
    // Lifecycle --------------------------------------------------------------
    /**
     * Constructor
     */
    function __construct($config = null) {
        $this->debug = Configure :: read() > 0;
        $this->fullDebug = Configure :: read() > 1;
        parent::__construct($config);
        return $this->connect();
    }
    
    /**
     * Destructor. Closes connection to the database.
     *
     */
    function __destruct() {
        $this->close();
        parent :: __destruct();
    }
    
    // I know this looks funny, and for other data sources this is necessary but for LDAP, we just return the name of the field we're passed as an argument
    function name( $field ) {
        return $field;
    }
    
    // Connection --------------------------------------------------------------
    function connect() {
        $config = $this->config;
        $this->connected = false;

        $this->connection = ldap_connect($config['host'], $config['port']);
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $config['version']);
        if (ldap_bind($this->connection, $config['login'], $config['password']))
            $this->connected = true;

        return $this->connected;
    }
    
    /**
     * Disconnects database, kills the connection and says the connection is closed,
     * and if DEBUG is turned on, the log for this object is shown.
     *
     */
    function close() {
        if ($this->fullDebug && Configure :: read() > 1) {
            $this->showLog();
        }
        $this->disconnect();
    }
    
    function disconnect() {
        @ldap_free_result($this->results);
        $this->connected = !@ldap_unbind($this->connection);
        return !$this->connected;
    }
    
    /**
     * Checks if it's connected to the database
     *
     * @return boolean True if the database is connected, else false
     */
    function isConnected() {
        return $this->connected;
    }
    
    /**
     * Reconnects to database server with optional new settings
     *
     * @param array $config An array defining the new configuration settings
     * @return boolean True on success, false on failure
     */
    function reconnect($config = null) {
        $this->disconnect();
        if ($config != null) {
            $this->config = am($this->_baseConfig, $this->config, $config);
        }
        return $this->connect();
    }

    // CRUD --------------------------------------------------------------
    /**
     * The "C" in CRUD
     *
     * @param Model $model
     * @param array $fields containing the field names
     * @param array $values containing the fields' values
     * @return true on success, false on error
     */
    function create( &$model, $fields = null, $values = null ) {
        $fieldsData = array();
        $id = null;
        $objectclasses = null;

        if ($fields == null) {
            unset($fields, $values);
            $fields = array_keys($model->data);
            $values = array_values($model->data);
        }
        
        $count = count($fields);
        
        for ($i = 0; $i < $count; $i++) {
            if ($fields[$i] == '_DN_') {
                $id = $values[$i];
            } else {
                $fieldsData[$fields[$i]] = $values[$i];
            }
        }

        if( !$id ) {
            $model->onError();
            return false;
        }

        // Add the entry
        if( @ldap_add( $this->connection, $id, $fieldsData ) ) {
            return true;
        } else {
            $model->onError();
            return false;
        }
    }

    /**
     * The "R" in CRUD
     *
     * @param Model $model
     * @param array $queryData
     * @param integer $recursive Number of levels of association
     * @return unknown
     */
    function read( &$model, $queryData = array(), $recursive = null ) {
        $this->__scrubQueryData($queryData);
        
        if (!is_null($recursive)) {
            $_recursive = $model->recursive;
            $model->recursive = $recursive;
        }

        // Check if we are doing a 'count' .. this is kinda ugly but i couldn't find a better way to do this, yet
        if ( is_string( $queryData['fields'] ) && $queryData['fields'] == 'COUNT(*) AS ' . $this->name( 'count' ) ) {
            // Check if we should also add a default object class to search under
            if( $model->defaultObjectClass ) {
                $queryData['conditions'] = sprintf( '(&(objectclass=%s)(%s=%s))', $model->defaultObjectClass, $model->primaryKey, array_shift( array_values( $queryData['conditions'] ) ) );
            } else {
                $queryData['conditions'] = sprintf( '%s=%s', $model->primaryKey, array_shift( array_values( $queryData['conditions'] ) ) );
            }
            $queryData['fields'] = array();
        }

        // Prepare query data ------------------------ 
        $queryData['conditions'] = $this->_conditions( $queryData['conditions'], $model);
        $queryData['targetDn'] = $model->useTable;
        $queryData['type'] = 'search';
        
        if (empty($queryData['order']))
                $queryData['order'] = array($model->primaryKey);
                    
        // Associations links --------------------------
        foreach ($model->__associations as $type) {
            foreach ($model->{$type} as $assoc => $assocData) {
                if ($model->recursive > -1) {
                    $linkModel = & $model->{$assoc};
                    $linkedModels[] = $type . '/' . $assoc;
                }
            }
        }
    
        // Execute search query ------------------------
        $res = $this->_executeQuery($queryData);
        if ($this->lastNumRows()==0) 
            return false;
        
        // Format results  -----------------------------
        ldap_sort($this->connection, $res, $queryData['order'][0]);
        $resultSet = ldap_get_entries($this->connection, $res);
        $resultSet = $this->_ldapFormat($model, $resultSet);    
    
        // Query on linked models  ----------------------
        if ($model->recursive > 0) {
            foreach ($model->__associations as $type) {
    
                foreach ($model->{$type} as $assoc => $assocData) {
                    $db = null;
                    $linkModel = & $model->{$assoc};
    
                    if ($model->useDbConfig == $linkModel->useDbConfig) {
                        $db = & $this;
                    } else {
                        $db = & ConnectionManager :: getDataSource($linkModel->useDbConfig);
                    }
    
                    if (isset ($db) && $db != null) {
                        $stack = array ($assoc);
                        $array = array ();
                        $db->queryAssociation($model, $linkModel, $type, $assoc, $assocData, $array, true, $resultSet, $model->recursive - 1, $stack);
                        unset ($db);
                    }
                }
            }
        }
        
        if (!is_null($recursive)) {
            $model->recursive = $_recursive;
        }

        // Add the count field to the resultSet (needed by find() to work out how many entries we got back .. used when $model->exists() is called)
        $resultSet[0][$model->alias]['count'] = $this->lastNumRows();
        
        return $resultSet;
    }

    /**
     * The "U" in CRUD
     */
    function update( &$model, $fields = null, $values = null ) {
        $fieldsData = array();

        if ($fields == null) {
            unset($fields, $values);
            $fields = array_keys( $model->data );
            $values = array_values( $model->data );
        }
        
        for ($i = 0; $i < count( $fields ); $i++) {
            $fieldsData[$fields[$i]] = $values[$i];
        }
        
        // Find the user we will update as we need their dn
        if( $model->defaultObjectClass ) {
            $queryData['conditions'] = sprintf( '(&(objectclass=%s)(%s=%s))', $model->defaultObjectClass, $model->primaryKey, $model->id );
        } else {
            $queryData['conditions'] = sprintf( '%s=%s', $model->primaryKey, $model->id );
        }
    
        // fetch the record
        $resultSet = $this->read( $model, $queryData, $model->recursive );
        
        if( $resultSet) {
            $_dn = $resultSet[0][$model->alias]['dn'];
            
            if( @ldap_modify( $this->connection, $_dn, $fieldsData ) ) {
                return true;
            }
        }
        
        // If we get this far, something went horribly wrong ..
        $model->onError();
        return false;
    }

    /**
     * The "D" in CRUD
     */    
    function delete( &$model ) {
        // Boolean to determine if we want to recursively delete or not
        $recursive = true;
        
        // Find the user we will update as we need their dn
        if( $model->defaultObjectClass ) {
            $queryData['conditions'] = sprintf( '(&(objectclass=%s)(%s=%s))', $model->defaultObjectClass, $model->primaryKey, $model->id );
        } else {
            $queryData['conditions'] = sprintf( '%s=%s', $model->primaryKey, $model->id );
        }
    
        // fetch the record
        $resultSet = $this->read( $model, $queryData, $model->recursive );
        
        if( $resultSet) {
            if( $recursive === true ) {
                // Recursively delete LDAP entries
                if( $this->__deleteRecursively( $resultSet[0]['LdapUser']['dn'] ) ) {
                    return true;
                }
            } else {
                // Single entry delete
                if( @ldap_delete( $this->connection, $resultSet[0]['LdapUser']['dn'] ) ) {
                    return true;
                }
            }
        }
        
        $model->onError();
        return false;
    }
    
    /* Courtesy of gabriel at hrz dot uni-marburg dot de @ http://ar.php.net/ldap_delete */
    function __deleteRecursively( $_dn ) {
        // Search for sub entries
        $subentries = ldap_list( $this->connection, $_dn, "objectClass=*", array() );
        $info = ldap_get_entries( $this->connection, $subentries );
        for( $i = 0; $i < $info['count']; $i++ ) {
            // deleting recursively sub entries
            $result = $this->__deleteRecursively( $info[$i]['dn'] );
            if( !$result ) {
                return false;
            }
        }
        
        return( @ldap_delete( $this->connection, $_dn ) );
    }
        
    // Public --------------------------------------------------------------    
    function generateAssociationQuery(& $model, & $linkModel, $type, $association = null, $assocData = array (), & $queryData, $external = false, & $resultSet) {
        $this->__scrubQueryData($queryData);
        
        switch ($type) {
            case 'hasOne' :
                $id = $resultSet[$model->name][$model->primaryKey];
                $queryData['conditions'] = trim($assocData['foreignKey']) . '=' . trim($id);
                $queryData['targetDn'] = $linkModel->useTable;
                $queryData['type'] = 'search';
                $queryData['limit'] = 1;

                return $queryData;
                
            case 'belongsTo' :
                $id = $resultSet[$model->name][$assocData['foreignKey']];
                $queryData['conditions'] = trim($linkModel->primaryKey).'='.trim($id);
                $queryData['targetDn'] = $linkModel->useTable;
                $queryData['type'] = 'search';
                $queryData['limit'] = 1;

                return $queryData;
                
            case 'hasMany' :
                $id = $resultSet[$model->name][$model->primaryKey];
                $queryData['conditions'] = trim($assocData['foreignKey']) . '=' . trim($id);
                $queryData['targetDn'] = $linkModel->useTable;
                $queryData['type'] = 'search';
                $queryData['limit'] = $assocData['limit'];

                return $queryData;

            case 'hasAndBelongsToMany' :
                return null;
        }
        return null;
    }

    function queryAssociation(& $model, & $linkModel, $type, $association, $assocData, & $queryData, $external = false, & $resultSet, $recursive, $stack) {
                    
        if (!isset ($resultSet) || !is_array($resultSet)) {
            if (Configure :: read() > 0) {
                e('<div style = "font: Verdana bold 12px; color: #FF0000">SQL Error in model ' . $model->name . ': ');
                if (isset ($this->error) && $this->error != null) {
                    e($this->error);
                }
                e('</div>');
            }
            return null;
        }
        
        $count = count($resultSet);
        for ($i = 0; $i < $count; $i++) {
            
            $row = & $resultSet[$i];
            $queryData = $this->generateAssociationQuery($model, $linkModel, $type, $association, $assocData, $queryData, $external, $row);
            $fetch = $this->_executeQuery($queryData);
            $fetch = ldap_get_entries($this->connection, $fetch);
            $fetch = $this->_ldapFormat($linkModel,$fetch);
            
            if (!empty ($fetch) && is_array($fetch)) {
                    if ($recursive > 0) {
                        foreach ($linkModel->__associations as $type1) {
                            foreach ($linkModel-> {$type1 } as $assoc1 => $assocData1) {
                                $deepModel = & $linkModel->{$assocData1['className']};
                                if ($deepModel->alias != $model->name) {
                                    $tmpStack = $stack;
                                    $tmpStack[] = $assoc1;
                                    if ($linkModel->useDbConfig == $deepModel->useDbConfig) {
                                        $db = & $this;
                                    } else {
                                        $db = & ConnectionManager :: getDataSource($deepModel->useDbConfig);
                                    }
                                    $queryData = array();
                                    $db->queryAssociation($linkModel, $deepModel, $type1, $assoc1, $assocData1, $queryData, true, $fetch, $recursive -1, $tmpStack);
                                }
                            }
                        }
                    }
                $this->__mergeAssociation($resultSet[$i], $fetch, $association, $type);

            } else {
                $tempArray[0][$association] = false;
                $this->__mergeAssociation($resultSet[$i], $tempArray, $association, $type);
            }
        }
    }
    
    /**
     * Returns a formatted error message from previous database operation.
     *
     * @return string Error message with error number
     */
    function lastError() {
        if (ldap_errno($this->connection)) {
            return ldap_errno($this->connection) . ': ' . ldap_error($this->connection);
        }
        return null;
    }

    /**
     * Returns number of rows in previous resultset. If no previous resultset exists,
     * this returns false.
     *
     * @return int Number of rows in resultset
     */
    function lastNumRows() {
        if ($this->_result and is_resource($this->_result)) {
            return @ ldap_count_entries($this->connection, $this->_result);
        }
        return null;
    }

    // Usefull public (static) functions--------------------------------------------    
    /**
     * Convert Active Directory timestamps to unix ones
     * 
     * @param integer $ad_timestamp Active directory timestamp
     * @return integer Unix timestamp
     */
    function convertTimestamp_ADToUnix($ad_timestamp) {
        $epoch_diff = 11644473600; // difference 1601<>1970 in seconds. see reference URL
        $date_timestamp = $ad_timestamp * 0.0000001;
        $unix_timestamp = $date_timestamp - $epoch_diff;
        return $unix_timestamp;
    }// convertTimestamp_ADToUnix
    
    /**
    * Returns an array of the attribute types defined in LDAP.
    *
    * @param object $model Not really used in this case ...
    * @return array Attribute types in LDAP. Keys are the name of the field as defined in LDAP
    */
    function describe(&$model) {
        $cache = null;
        if ($this->cacheSources !== false) {
            if (isset($this->__descriptions['ldap_attributetypes'])) {
                $cache = $this->__descriptions['ldap_attributetypes'];
            } else {
                $cache = $this->__cacheDescription('attributetypes');
            }
        }
                        
        if ($cache != null) {
            return $cache;
        }
        
        // If we get this far, then we haven't cached the attribute types, yet!
        $attrs = Set::combine( $this->__getLDAPschema(), 'attributetypes.{n}.name', 'attributetypes.{n}.description' );
        $attrs['_DN_'] = 'Distinguished Name';
    
        // Cache away
        $this->__cacheDescription( 'attributetypes', $attrs );
        
        return $attrs;
    }

    /* The following was kindly "borrowed" from the excellent phpldapadmin project */
    function __getLDAPschema() {
        $schemaTypes = array( 'objectclasses', 'attributetypes' );
        foreach (array('(objectClass=*)','(objectClass=subschema)') as $schema_filter) {
            $schema_search = @ldap_read($this->connection, 'cn=Subschema', $schema_filter, $schemaTypes,0,0,0,LDAP_DEREF_ALWAYS);
            
            if( is_null( $schema_search ) ) {
                $this->log( "LDAP schema filter $schema_filter is invalid!" );
                continue;
            }
            
            $schema_entries = @ldap_get_entries( $this->connection, $schema_search );
            
            if ( is_array( $schema_entries ) && isset( $schema_entries['count'] ) ) {
                break;
            }
            
            unset( $schema_entries );
            $schema_search = null;
        }
 
           if( $schema_entries ) {
               $return = array();
               foreach( $schemaTypes as $n ) {
                $schemaTypeEntries = $schema_entries[0][$n];
                for( $x = 0; $x < $schemaTypeEntries['count']; $x++ ) {
                    $entry = array();
                    $strings = preg_split('/[\s,]+/', $schemaTypeEntries[$x], -1, PREG_SPLIT_DELIM_CAPTURE);
                    $str_count = count( $strings );
                    for ( $i=0; $i < $str_count; $i++ ) {
                        switch ($strings[$i]) {
                            case '(':
                                break;
                            case 'NAME':
                                if ( $strings[$i+1] != '(' ) {
                                    do {
                                        $i++;
                                            if( !isset( $entry['name'] ) || strlen( $entry['name'] ) == 0 )
                                                $entry['name'] = $strings[$i];
                                            else
                                                $entry['name'] .= ' '.$strings[$i];
                                    } while ( !preg_match('/\'$/s', $strings[$i]));
                                } else {
                                    $i++;
                                    do {
                                        $i++;
                                        if( !isset( $entry['name'] ) || strlen( $entry['name'] ) == 0)
                                            $entry['name'] = $strings[$i];
                                        else
                                            $entry['name'] .= ' ' . $strings[$i];
                                    } while ( !preg_match( '/\'$/s', $strings[$i] ) );
                                    do {
                                        $i++;
                                    } while ( !preg_match( '/\)+\)?/', $strings[$i] ) );
                                }
    
                                $entry['name'] = preg_replace('/^\'/', '', $entry['name'] );
                                $entry['name'] = preg_replace('/\'$/', '', $entry['name'] );
                                break;
                            case 'DESC':
                                do {
                                    $i++;
                                    if ( !isset( $entry['description'] ) || strlen( $entry['description'] ) == 0 )
                                        $entry['description'] = $strings[$i];
                                    else
                                        $entry['description'] .= ' ' . $strings[$i];
                                } while ( !preg_match( '/\'$/s', $strings[$i] ) );
                                break;
                            case 'OBSOLETE':
                                $entry['is_obsolete'] = TRUE;
                                break;
                            case 'SUP':
                                $entry['sup_classes'] = array();
                                if ( $strings[$i+1] != '(' ) {
                                    $i++;
                                    array_push( $entry['sup_classes'], preg_replace( "/'/", '', $strings[$i] ) );
                                } else {
                                    $i++;
                                    do {
                                        $i++;
                                        if ( $strings[$i] != '$' )
                                            array_push( $entry['sup_classes'], preg_replace( "/'/", '', $strings[$i] ) );
                                    } while (! preg_match('/\)+\)?/',$strings[$i+1]));
                                }
                                break;
                            case 'ABSTRACT':
                                $entry['type'] = 'abstract';
                                break;
                            case 'STRUCTURAL':
                                $entry['type'] = 'structural';
                                break;
                            case 'AUXILIARY':
                                $entry['type'] = 'auxiliary';
                                break;
                            case 'MUST':
                                $entry['must'] = array();

                                $i = $this->_parse_list(++$i, $strings, $entry['must']);

                                break;

                            case 'MAY':
                                $entry['may'] = array();

                                $i = $this->_parse_list(++$i, $strings, $entry['may']);

                                break;
                            default:
                                if( preg_match( '/[\d\.]+/i', $strings[$i]) && $i == 1 ) {
                                    $entry['oid'] = $strings[$i];
                                }
                                break;
                        }
                    }
                    if( !isset( $return[$n] ) || !is_array( $return[$n] ) ) {
                        $return[$n] = array();
                    }
                    array_push( $return[$n], $entry );
                }
            }
        }

//        $fields = Set::combine( $attributes, '{n}.name', '{n}.description' );
//        $fields['dn'] = 'DN of the entry in question';
        
        return $return;
    }

    function _parse_list( $i, $strings, &$attrs ) {
        /**
         ** A list starts with a ( followed by a list of attributes separated by $ terminated by )
         ** The first token can therefore be a ( or a (NAME or a (NAME)
         ** The last token can therefore be a ) or NAME)
         ** The last token may be terminate by more than one bracket
         */
        $string = $strings[$i];
        if (!preg_match('/^\(/',$string)) {
            // A bareword only - can be terminated by a ) if the last item
            if (preg_match('/\)+$/',$string))
                    $string = preg_replace('/\)+$/','',$string);

            array_push($attrs, $string);
        } elseif (preg_match('/^\(.*\)$/',$string)) {
            $string = preg_replace('/^\(/','',$string);
            $string = preg_replace('/\)+$/','',$string);
            array_push($attrs, $string);
        } else {
            // Handle the opening cases first
            if ($string == '(') {
                    $i++;

            } elseif (preg_match('/^\(./',$string)) {
                    $string = preg_replace('/^\(/','',$string);
                    array_push ($attrs, $string);
                    $i++;
            }

            // Token is either a name, a $ or a ')'
            // NAME can be terminated by one or more ')'
            while (! preg_match('/\)+$/',$strings[$i])) {
                    $string = $strings[$i];
                    if ($string == '$') {
                            $i++;
                            continue;
                    }

                    if (preg_match('/\)$/',$string)) {
                            $string = preg_replace('/\)+$/','',$string);
                    } else {
                            $i++;
                    }
                    array_push ($attrs, $string);
            }
        }
        sort($attrs);

        return $i;
    }

    /**
     * Function not supported
     */
    function execute($query) {
        return null;
    }
    
    /**
     * Function not supported
     */
    function fetchAll($query, $cache = true) {
        return array();
    }
    
    // Logs --------------------------------------------------------------
    /**
     * Log given LDAP query.
     *
     * @param string $query LDAP statement
     * @todo: Add hook to log errors instead of returning false
     */
    function logQuery($query) {
        $this->_queriesCnt++;
        $this->_queriesTime += $this->took;
        $this->_queriesLog[] = array (
            'query' => $query,
            'error' => $this->error,
            'affected' => $this->affected,
            'numRows' => $this->numRows,
            'took' => $this->took
        );
        if (count($this->_queriesLog) > $this->_queriesLogMax) {
            array_pop($this->_queriesLog);
        }
        if ($this->error) {
            return false;
        }
    }
    
    /**
     * Outputs the contents of the queries log.
     *
     * @param boolean $sorted
     */
    function showLog($sorted = false) {
        if ($sorted) {
            $log = sortByKey($this->_queriesLog, 'took', 'desc', SORT_NUMERIC);
        } else {
            $log = $this->_queriesLog;
        }

        if ($this->_queriesCnt > 1) {
            $text = 'queries';
        } else {
            $text = 'query';
        }

        if (php_sapi_name() != 'cli') {
            print ("<table id=\"cakeSqlLog\" cellspacing=\"0\" border = \"0\">\n<caption>{$this->_queriesCnt} {$text} took {$this->_queriesTime} ms</caption>\n");
            print ("<thead>\n<tr><th>Nr</th><th>Query</th><th>Error</th><th>Affected</th><th>Num. rows</th><th>Took (ms)</th></tr>\n</thead>\n<tbody>\n");

            foreach ($log as $k => $i) {
                print ("<tr><td>" . ($k +1) . "</td><td>{$i['query']}</td><td>{$i['error']}</td><td style = \"text-align: right\">{$i['affected']}</td><td style = \"text-align: right\">{$i['numRows']}</td><td style = \"text-align: right\">{$i['took']}</td></tr>\n");
            }
            print ("</table>\n");
        } else {
            foreach ($log as $k => $i) {
                print (($k +1) . ". {$i['query']} {$i['error']}\n");
            }
        }
    }

    /**
     * Output information about a LDAP query. The query, number of rows in resultset,
     * and execution time in microseconds. If the query fails, an error is output instead.
     *
     * @param string $query Query to show information on.
     */
    function showQuery($query) {
        $error = $this->error;
        if (strlen($query) > 200 && !$this->fullDebug) {
            $query = substr($query, 0, 200) . '[...]';
        }

        if ($this->debug || $error) {
            print ("<p style = \"text-align:left\"><b>Query:</b> {$query} <small>[Aff:{$this->affected} Num:{$this->numRows} Took:{$this->took}ms]</small>");
            if ($error) {
                print ("<br /><span style = \"color:Red;text-align:left\"><b>ERROR:</b> {$this->error}</span>");
            }
            print ('</p>');
        }
    }
    
    // _ private --------------------------------------------------------------
    function _conditions($conditions, $model) {
        $res = '';
        $key = $model->primaryKey;
        $name = $model->name;
        if (is_array($conditions)) {
            // Conditions expressed as an array 
            if (empty($conditions))
                $conditions = array ('equals'=>array($key => null));
            
            $res = $this->__conditionsArrayToString($conditions);
        } else {
            // "valid" ldap search expression
            if (!strpos ($conditions, '=')) 
                $conditions = $key . '=' . trim($conditions);
                
            $res = str_replace ( array("$name.$key"," = "), array($key,"="), $conditions );
        }
        return $res;
    }
    /**
     * Convert an array into a ldap condition string
     * 
     * @param array $conditions condition 
     * @return string 
     */
    function __conditionsArrayToString($conditions) {
        $ops_rec = array ( 'and' => array('prefix'=>'&'), 'or' => array('prefix'=>'|'));
        $ops_neg = array ( 'and not' => array() , 'or not' => array(), 'not equals' => array());
        $ops_ter = array ( 'equals' => array('null'=>'*'));
        
        $ops = array_merge($ops_rec,$ops_neg, $ops_ter);
        
        if (is_array($conditions)) {
            
            $operand = array_keys($conditions);
            $operand = $operand[0];
            
            if (!in_array($operand,array_keys($ops)) )
                return null;
            
            $children = $conditions[$operand];
            
            if (in_array($operand, array_keys($ops_rec)) ) {
                if (!is_array($children))
                    return null;
            
                $tmp = '('.$ops_rec[$operand]['prefix'];
                foreach ($children as $key => $value)  {
                    $child = array ($key => $value);
                    $tmp .= $this->__conditionsArrayToString($child);
                }
                return $tmp.')';
                
            } else if (in_array($operand, array_keys($ops_neg)) ) {
                    if (!is_array($children))
                        return null;
                        
                    $next_operand = trim(str_replace('not', '', $operand));
                    
                    return '(!'.$this->__conditionsArrayToString(array ($next_operand => $children)).')';
                    
            } else if (in_array($operand,  array_keys($ops_ter)) ){
                    $tmp = '';
                    foreach ($children as $key => $value) {
                        if ( !is_array($value) )
                            $tmp .= '('.$key .'='.((is_null($value))?$ops_ter['equals']['null']:$value).')';
                        else
                            foreach ($value as $subvalue) 
                                $tmp .= $this->__conditionsArrayToString(array('equals' => array($key => $subvalue)));
                    }
                    return $tmp;
            }            
        }
    }
    
    function _executeQuery($queryData = array (), $cache = true) {
        $t = getMicrotime();
        $query = $this->_queryToString($queryData);
        if ($cache && isset ($this->_queryCache[$query])) {
            if (strpos(trim(strtolower($query)), $queryData['type']) !== false) {
                $res = $this->_queryCache[$query];
            }
        } else {        
            switch ($queryData['type']) {
                case 'search':
                    // TODO pb ldap_search & $queryData['limit']
                    if ($res = @ ldap_search($this->connection, ( ( $queryData['targetDn'] ) ? $queryData['targetDn'] . ',' : null ) . $this->config['basedn'], $queryData['conditions'], $queryData['fields'], 0, $queryData['limit'])) {
                        if ($cache) {
                            if (strpos(trim(strtolower($query)), $queryData['type']) !== false) {
                                $this->_queryCache[$query] = $res;
                            }
                        }
                    } else{
                        $res = false;
                    }
                    break;
                case 'delete':
                    $res = @ ldap_delete($this->connection, $queryData['targetDn'] . ',' . $this->config['basedn']);             
                    break;
                default:
                    $res = false;
                    break;
            }
        }
                
        $this->_result = $res;
        $this->took = round((getMicrotime() - $t) * 1000, 0);
        $this->error = $this->lastError();
        $this->numRows = $this->lastNumRows();

        if ($this->fullDebug) {
            $this->logQuery($query);
        }

        return $this->_result;
    }
    
    function _queryToString($queryData) {
        $tmp = '';
        if (!empty($queryData['conditions'])) 
            $tmp .= ' | cond: '.$queryData['conditions'].' ';

        if (!empty($queryData['targetDn'])) 
            $tmp .= ' | targetDn: '.$queryData['targetDn'].','.$this->config['basedn'].' ';

        $fields = '';
        if (!empty($queryData['fields']) && is_array( $queryData['fields'] ) ) {
            $fields .= ' | fields: ';
            foreach ($queryData['fields'] as $field)
                $fields .= ' ' . $field;
            $tmp .= $queryData['fields'].' ';
        }
    
        if (!empty($queryData['order']))         
            $tmp .= ' | order: '.$queryData['order'][0].' ';

        if (!empty($queryData['limit']))
            $tmp .= ' | limit: '.$queryData['limit'];

        return $queryData['type'] . $tmp;
    }

    function _ldapFormat(& $model, $data) {
        $res = array ();

        foreach ($data as $key => $row){
            if ($key === 'count')
                continue;
    
            foreach ($row as $key1 => $param){
                if ($key1 === 'dn') {
                    $res[$key][$model->name][$key1] = $param;
                    continue;
                }
                if (!is_numeric($key1))
                    continue;
                if ($row[$param]['count'] === 1)
                    $res[$key][$model->name][$param] = $row[$param][0];
                else {
                    foreach ($row[$param] as $key2 => $item) {
                        if ($key2 === 'count')
                            continue;
                        $res[$key][$model->name][$param][] = $item;
                    }
                }
            }
        }
        return $res;
    }
    
    function _ldapQuote($str) {
        return str_replace(
                array( '\\', ' ', '*', '(', ')' ),
                array( '\\5c', '\\20', '\\2a', '\\28', '\\29' ),
                $str
        );
    }
    
    // __ -----------------------------------------------------
    function __mergeAssociation(& $data, $merge, $association, $type) {
                
        if (isset ($merge[0]) && !isset ($merge[0][$association])) {
            $association = Inflector :: pluralize($association);
        }

        if ($type == 'belongsTo' || $type == 'hasOne') {
            if (isset ($merge[$association])) {
                $data[$association] = $merge[$association][0];
            } else {
                if (count($merge[0][$association]) > 1) {
                    foreach ($merge[0] as $assoc => $data2) {
                        if ($assoc != $association) {
                            $merge[0][$association][$assoc] = $data2;
                        }
                    }
                }
                if (!isset ($data[$association])) {
                    $data[$association] = $merge[0][$association];
                } else {
                    if (is_array($merge[0][$association])) {
                        $data[$association] = array_merge($merge[0][$association], $data[$association]);
                    }
                }
            }
        } else {
            if ($merge[0][$association] === false) {
                if (!isset ($data[$association])) {
                    $data[$association] = array ();
                }
            } else {
                foreach ($merge as $i => $row) {
                    if (count($row) == 1) {
                        $data[$association][] = $row[$association];
                    } else {
                        $tmp = array_merge($row[$association], $row);
                        unset ($tmp[$association]);
                        $data[$association][] = $tmp;
                    }
                }
            }
        }
    }
    
    /**
     * Private helper method to remove query metadata in given data array.
     *
     * @param array $data
     */
    function __scrubQueryData(& $data) {
        if (!isset ($data['type']))
            $data['type'] = 'default';
        
        if (!isset ($data['conditions'])) 
            $data['conditions'] = array();

        if (!isset ($data['targetDn'])) 
            $data['targetDn'] = null;
    
        if (!isset ($data['fields']) && empty($data['fields'])) 
            $data['fields'] = array ();
        
        if (!isset ($data['order']) && empty($data['order'])) 
            $data['order'] = array ();

        if (!isset ($data['limit']))
            $data['limit'] = null;
    }
    
    function __getObjectclasses() {
        $cache = null;
        if ($this->cacheSources !== false) {
            if (isset($this->__descriptions['ldap_objectclasses'])) {
                $cache = $this->__descriptions['ldap_objectclasses'];
            } else {
                $cache = $this->__cacheDescription('objectclasses');
            }
        }
                        
        if ($cache != null) {
            return $cache;
        }
        
        // If we get this far, then we haven't cached the attribute types, yet!
        $ldapschema = $this->__getLDAPschema();
        $objectclasses = $ldapschema['objectclasses'];
        
        // Cache away
        $this->__cacheDescription( 'objectclasses', $objectclasses );
        
        return $objectclasses;
    }
  
    // This was an attempt to automatically get the objectclass that an attribute belongs to. Unfortunately, more than one objectclass
    // can define the same attribute as a MAY or MUST which means it's impossible to know which objectclass is the right one.
    // Due to this problem (which I only realized once I had it working and it was returning objectclasses I wasn't interested in), this
    // function is no longer in use. Objectclasses must be defined inside $this->data when calling $this->save.
    function __getObjectclassForAttribute( $attr, &$ret = array() ) {
        $res = null;
        if ($this->cacheSources !== false) {
            if (isset($this->__descriptions['ldap_attributes_for_objectclasses'])) {
                $res = $this->__descriptions['ldap_attributes_for_objectclasses'];
            } else {
                $res = $this->__cacheDescription('attributes_for_objectclasses');
            }
        }
                        
        if ($res == null) {
            $objectclasses = $this->__getObjectclasses();
            $musts = Set::combine( $objectclasses, '{n}.name', '{n}.must' );
            $mays  = Set::combine( $objectclasses, '{n}.name', '{n}.may' );
            
            $attributes = array();
            
            // Please feel free to suggest a better way of doing this
            foreach( array( 'musts', 'mays' ) as $n ) {
                foreach( ${$n} as $_key => $_vals ) {
                    if( !isset( $attributes[$_key] ) ) {
                        $attributes[$_key] = array();
                    }
                    if( is_array( $_vals ) ) {
                        foreach( $_vals as $_val ) {
                            array_push( $attributes[$_key], $_val );
                        }
                    }
                }
            }
        
            // Cache away
            $this->__cacheDescription( 'attributes_for_objectclasses', $attributes );
            
            $res =& $attributes;
        }
        
        // Now we check if the attribute type exists and what objectclass it's found in
        if( is_array( $attr ) ) {
            foreach( $attr as $x ) {
                $this->__getObjectclassForAttribute( $x, $ret );
            }
        } else {
            foreach( $res as $obj => $attrs ) {
                if( in_array( $attr, $attrs ) ) {
                    if( !isset( $ret[$obj] ) ) {
                        $ret[$obj] = 1;
                    }
                    return $ret;
                }
            }
        }
        
        return $ret;
    }
    
    function boolean() {
        return null;
    }

} // LdapSource
?>

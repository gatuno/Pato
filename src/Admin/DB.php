<?php

function Admin_DB_getDB () {
	$admdb = 'DB_admin';
	if (isset ($GLOBALS[$admdb])) {
		return $GLOBALS[$admdb];
	}
	
	$local_db = clone (Gatuf::db ());
	$local_db->dbname = $local_db->dbname.'_admin';
	
	$GLOBALS[$admdb] = $local_db;
	
	return $local_db;
}

<?php

function Admision_DB_getDB () {
	$aspdb = 'DB_aspirante';
	if (isset ($GLOBALS[$aspdb])) {
		return $GLOBALS[$aspdb];
	}
	
	$local_db = clone (Gatuf::db ());
	$local_db->dbname = $local_db->dbname.'_aspirantes';
	
	$GLOBALS[$aspdb] = $local_db;
	
	return $local_db;
}

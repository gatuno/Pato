<?php

function CP_DB_getDB () {
	$cpdb = 'DB_postal';
	if (isset ($GLOBALS[$cpdb])) {
		return $GLOBALS[$cpdb];
	}
	
	$local_db = clone (Gatuf::db ());
	$local_db->dbname = 'sepomex';
	
	$GLOBALS[$cpdb] = $local_db;
	
	return $local_db;
}

<?php
Gatuf::loadFunction ('CP_DB_getDB');

function CP_Migrations_Install_setup ($params=null) {
	$models = array ('CP_Asentamiento',
	                 'CP_CP',
	                 'CP_Estado',
	                 'CP_Municipio',
	                 'CP_Pais',
	                 'CP_Zona',
	                 );
	
	$db = CP_DB_getDB ();
	$schema = new Gatuf_DB_Schema ($db);
	foreach ($models as $model) {
		$schema->model = new $model ();
		$schema->createTables ();
	}
	
	foreach ($models as $model) {
		$schema->model = new $model ();
		$schema->createConstraints ();
	}
}

function CP_Migrations_Install_teardown ($params=null) {
	$models = array ('CP_Asentamiento',
	                 'CP_CP',
	                 'CP_Estado',
	                 'CP_Municipio',
	                 'CP_Pais',
	                 'CP_Zona',
	                 );
	
	$db = CP_DB_getDB ();
	$schema = new Gatuf_DB_Schema ($db);
	
	foreach ($models as $model) {
		$schema->model = new $model ();
		$schema->dropConstraints();
	}
	
	foreach ($models as $model) {
		$schema->model = new $model ();
		$schema->dropTables ();
	}
}

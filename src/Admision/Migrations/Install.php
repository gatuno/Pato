<?php
Gatuf::loadFunction ('Admision_DB_getDB');

function Admision_Migrations_Install_setup ($params=null) {
	$models = array ('Admision_Aspirante',
	                 'Admision_Estadistica',
	                 'Admision_Convocatoria',
	                 'Admision_CupoCarrera',
	                 'Admision_Documento',
	                 );
	
	$db = Admision_DB_getDB ();
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

function Admision_Migrations_Install_teardown ($params=null) {
	$models = array ('Admision_Aspirante',
	                 'Admision_Estadistica',
	                 'Admision_Convocatoria',
	                 'Admision_CupoCarrera',
	                 'Admision_Documento',
	                 );
	
	$db = Admision_DB_getDB ();
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

<?php
Gatuf::loadFunction ('Admin_DB_getDB');

function Admin_Migrations_Install_setup ($params=null) {
	$models = array ('Admin_SI_Laboratorio',
	                 'Admin_SI_LaboratorioIngreso',
	                 );
	
	$db = Admin_DB_getDB ();
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

function Admin_Migrations_Install_teardown ($params=null) {
	$models = array ('Admin_SI_Laboratorio',
	                 'Admin_SI_LaboratorioIngreso',
	                 );
	
	$db = Admin_DB_getDB ();
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

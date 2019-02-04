<?php

function Pato_Migrations_Install_setup ($params=null) {
	$models = array ('Pato_Alumno',
	                 'Pato_Aviso',
	                 'Pato_Calendario',
	                 'Pato_Carrera',
	                 'Pato_DiaFestivo',
	                 'Pato_Edificio',
	                 'Pato_Estatus',
	                 'Pato_Evaluacion',
	                 'Pato_GPE',
	                 'Pato_Inscripcion',
	                 'Pato_InscripcionEstatus',
	                 'Pato_Kardex',
	                 //'Pato_Maestro', -> Gatuf lo crea
	                 'Pato_Materia',
	                 'Pato_MessageA',
	                 'Pato_Salon',
	                 'Pato_PerfilAlumno',
	                 );
	$db = Gatuf::db ();
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

function Pato_Migrations_Install_teardown ($params=null) {
	$models = array ('Pato_Alumno',
	                 'Pato_Aviso',
	                 'Pato_Calendario',
	                 'Pato_Carrera',
	                 'Pato_DiaFestivo',
	                 'Pato_Edificio',
	                 'Pato_Estatus',
	                 'Pato_Evaluacion',
	                 'Pato_GPE',
	                 'Pato_Inscripcion',
	                 'Pato_InscripcionEstatus',
	                 'Pato_Kardex',
	                 //'Pato_Maestro', -> Gatuf lo crea
	                 'Pato_Materia',
	                 'Pato_MessageA',
	                 'Pato_Salon',
	                 'Pato_PerfilAlumno',
	                 );
	
	Pato_Migrations_Install_1Vistas_teardown ();
	
	$db = Gatuf::db ();
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

function Pato_Migrations_Install_2GruposEval_setup ($params = null) {
	$geval = new Pato_GrupoEvaluacion ();
	
	/* Crear las tres primeros y necesarios grupos de evaluacion */
	$grupos = array (1 => 'Ordinario',
	                 2 => 'Extraordinario',
	                 3 => 'Verano');
	
	foreach ($grupos as $id => $descripcion) {
		$geval->id = $id;
		$geval->descripcion = $descripcion;
		
		$geval->create ();
	}
}

function Pato_Migrations_Install_6Carreras_setup ($params = null) {
	$carrera_model = new Pato_Carrera ();
	
	$carreras = array ('BIM' => array ('Ingeniería en Biomédica', 'DIVEC'),
	                   'CEL' => array ('Ingeniería en Electrónica y Comunicaciones', 'DIVEC'),
	                   'CIV' => array ('Ingeniería Civil', 'DIVING'),
	                   'COM' => array ('Ingenieria en Computación', 'DIVEC'),
	                   'DCEC' => array ('Doctorado en Ciencias de la Electrónica y la Computación', 'DIVEC'),
	                   'FIS' => array ('Licenciatura en Física', 'DIVBASICAS'),
	                   'INBI' => array ('Ingeniería en Biomédica (Nueva)', 'DIVEC'),
	                   'INCE' => array ('Ingeniería en Electrónica y Comunicaciones (Nueva)', 'DIVEC'),
	                   'INCO' => array ('Ingeniería en Computación (Nueva)', 'DIVEC'),
	                   'IND' => array ('Ingeniería Industrial', 'DIVING'),
	                   'INDU' => array ('Ingeniería Industrial (Nueva)', 'DIVING'),
	                   'INF' => array ('Licenciatura en Informática', 'DIVEC'),
	                   'INME' => array ('Ingeniería Mecánica Eléctrica (Nueva)', 'DIVING'),
	                   'INNI' => array ('Ingeniería en Informática (Nueva)', 'DIVEC'),
	                   'INQU' => array ('Ingeniería Química (Nueva)', 'DIVING'),
	                   'IQU' => array ('Ingeniería Química', 'DIVING'),
	                   'LIFI' => array ('Licenciatura en Física (Nueva)', 'DIVBASICAS'),
	                   'LIMA' => array ('Licenciatura en Matemáticas (Nueva)', 'DIVBASICAS'),
	                   'LINA' => array ('Licenciatura en Ingeniería en Alimentos y Biotecnología', 'DIVING'),
	                   'LQFB' => array ('Licenciatura en Quimico Farmaceutico Biologo (Nueva)', 'DIVBASICAS'),
	                   'LQUI' => array ('Licenciatura en Química (Nueva)', 'DIVBASICAS'),
	                   'MAT' => array ('Licenciatura en Matemáticas', 'DIVBASICAS'),
	                   'MEL' => array ('Ingeniería Mecánica Eléctrica', 'DIVING'),
	                   'MIEC' => array ('Maestría en Ciencias en Ingeniería Electrónica y Computación', 'DIVBASICAS'),
	                   'QFB' => array ('Licenciatura en Químico Farmacobiólogo', 'DIVBASICAS'),
	                   'QUI' => array ('Licenciatura en Química', 'DIVBASICAS'),
	                   'TOP' => array ('Ingeniería en Topografía', 'DIVING')
	                   );
	
	foreach ($carreras as $clave => $data) {
		$carrera_model->clave = $clave;
		$carrera_model->descripcion = $data[0];
		$carrera_model->color = 0;
		
		$carrera_model->create (); /* NO raw para que los permisos se creen automáticamente */
	}
}

function Pato_Migrations_Install_5Edificios_setup ($params = null) {
	$edificio_model = new Pato_Edificio ();
	
	$edificios = array ();
	
	foreach ($edificios as $clave => $descripcion) {
		$edificio_model->clave = $clave;
		$edificio_model->descripcion = $descripcion;
		
		$edificio_model->create (true);
	}
}


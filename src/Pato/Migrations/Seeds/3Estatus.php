<?php

function Pato_Migrations_Seeds_3Estatus_run ($params=null) {
	/* Crear los estatus de los alumnos */
	$estatus = array (
		array ('AC', 'Activo', 1, 0),
		array ('AI', 'Alumno ausente por Intercambio', 1, 0),
		array ('B2', 'Admitido pero sin Documentos', 0, 0),
		array ('B5', 'Baja académica', 0, 0),
		array ('B6', 'Baja administrativa', 0, 1),
		array ('BV', 'Baja voluntaria', 0, 0),
		array ('CC', 'Cambio de carrera', 0, 0),
		array ('EG', 'Egresado', 0, 0),
		array ('FI', 'Terminación de Intercambio', 0, 0),
		array ('LI', 'Licencia', 1, 0),
		array ('TT', 'Titulado', 0, 0),
		array ('XI', 'Alumno recibido por Intercambio', 1, 0),
	);
	
	foreach ($estatus as $e) {
		$est = new Pato_Estatus ();
		
		$est->clave = $e[0];
		$est->descripcion = $e[1];
		$est->activo = $e[2];
		$est->oculto = $e[3];
		
		$est->create ();
	}
}

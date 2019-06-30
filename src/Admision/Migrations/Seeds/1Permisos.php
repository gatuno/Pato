<?php

function Admision_Migrations_Seeds_1Permisos_run ($params=null) {
	$lista = array (
		array ('admin_aspirantes', 'Administrar aspirantes', 'Permite al usuario modificar datos de aspirantes'),
		array ('admin_convocatoria', 'Administrar convocatorias de admisión', 'Permite al usuario crear y modificar convocatorias de admisión'),
		array ('admitir_aspirantes', 'Admitir aspirantes', 'Permite al usuario admitir aspirantes en el proceso de admisión'),
		array ('agregar_codigos_postales', 'Configurar códigos postales', 'Permite agregar códigos postales'),
	);
	
	foreach ($lista as $l) {
		$permiso = new Gatuf_Permission ();
		
		$permiso->code_name = $l[0];
		$permiso->name = $l[1];
		$permiso->description = $l[2];
		$permiso->application = 'Admision';
		
		$permiso->create ();
	}
}

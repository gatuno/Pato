<?php

function Pato_Migrations_Seeds_2GPE_run ($params=null) {
	$geval = new Pato_GPE ();
	
	/* Crear las tres primeros y necesarios grupos de evaluacion */
	$grupos = array (
		array (1, 'Ordinario', 'AB'),
		array (2, 'Extraordinario', ''),
	);
	
	foreach ($grupos as $g) {
		$geval->id = $g[0];
		$geval->descripcion = $g[1];
		$geval->secciones = $g[2];
		
		$geval->create ();
	}
}

<?php
$base = Gatuf::config('pato_base');
$ctl_a = array ();

/* Bloque base:
$ctl_a[] = array (
	'regex' => '#^/ /$#',
	'base' => $base,
	'model' => 'Admin_Views_',
	'method' => '',
);
*/

$ctl_a[] = array (
	'regex' => '#^/$#',
	'base' => $base,
	'model' => 'Admin_Views',
	'method' => 'index',
);

$ctl_a[] = array (
	'regex' => '#^/si/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI',
	'method' => 'index',
);

$ctl_a[] = array (
	'regex' => '#^/si/laboratorios/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI_Laboratorio',
	'method' => 'index',
);

$ctl_a[] = array (
	'regex' => '#^/si/laboratorios/add/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI_Laboratorio',
	'method' => 'agregar',
);

$ctl_a[] = array (
	'regex' => '#^/si/laboratorio/(\d+)/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI_Laboratorio',
	'method' => 'ver',
);

$ctl_a[] = array (
	'regex' => '#^/si/laboratorio/(\d+)/registro/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI_Laboratorio',
	'method' => 'registrar',
);

$ctl_a[] = array (
	'regex' => '#^/si/laboratorio/(\d+)/registro/entrada/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI_Laboratorio',
	'method' => 'registrarEntrada',
);

$ctl_a[] = array (
	'regex' => '#^/si/laboratorio/(\d+)/registro/salida/$#',
	'base' => $base,
	'model' => 'Admin_Views_SI_Laboratorio',
	'method' => 'registrarSalida',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca',
	'method' => 'index',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/equipo/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca_Equipo',
	'method' => 'index',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/equipo/add/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca_Equipo',
	'method' => 'agregar',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/equipo/(\d+)/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca_Equipo',
	'method' => 'ver',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/prestar/(\d+)/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca_Equipo',
	'method' => 'prestar',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/equipo/(\d+)/regresar/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca_Equipo',
	'method' => 'regresarPorEquipo',
);

$ctl_a[] = array (
	'regex' => '#^/biblioteca/prestamo/(\d+)/regresar/$#',
	'base' => $base,
	'model' => 'Admin_Views_Biblioteca_Equipo',
	'method' => 'regresarPorPrestamo',
);

return $ctl_a;

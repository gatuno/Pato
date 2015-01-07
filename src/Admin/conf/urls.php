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

return $ctl_a;

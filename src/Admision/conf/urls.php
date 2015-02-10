<?php
$base = Gatuf::config('pato_base');
$ctl_ad = array ();

/* Bloque base:
$ctl_ad[] = array (
	'regex' => '#^/ /$#',
	'base' => $base,
	'model' => 'Admision_Views_',
	'method' => '',
);
*/

$ctl_ad[] = array (
	'regex' => '#^/$#',
	'base' => $base,
	'model' => 'Admision_Views',
	'method' => 'index',
);

$ctl_ad[] = array (
	'regex' => '#^/registro/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'convocatoria',
);

$ctl_ad[] = array (
	'regex' => '#^/registro/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'registro',
);

$ctl_ad[] = array (
	'regex' => '#^/postregistro/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'postRegistro',
);

$ctl_ad[] = array (
	'regex' => '#^/convocatorias/$#',
	'base' => $base,
	'model' => 'Admision_Views_Convocatoria',
	'method' => 'index',
);

$ctl_ad[] = array (
	'regex' => '#^/convocatorias/add/$#',
	'base' => $base,
	'model' => 'Admision_Views_Convocatoria',
	'method' => 'agregar',
);

$ctl_ad[] = array (
	'regex' => '#^/convocatoria/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Convocatoria',
	'method' => 'ver',
);

$ctl_ad[] = array (
	'regex' => '#^/convocatoria/(\d+)/agregar-carrera/$#',
	'base' => $base,
	'model' => 'Admision_Views_Convocatoria',
	'method' => 'agregarCupo',
);

return $ctl_ad;

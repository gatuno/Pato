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

$ctl_ad[] = array (
	'regex' => '#^/aspirantes/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'index',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirantes/JSON/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'buscarJSON',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirantes/continuar/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'continuar',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'ver',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/(\d+)/subir-foto/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'subirFoto',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/(\d+)/foto/miniatura/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'verFotoMiniatura',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirantes/logout/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'cerrar',
);

return $ctl_ad;

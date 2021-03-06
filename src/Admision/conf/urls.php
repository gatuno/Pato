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
	'model' => 'Admision_Views_Index',
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
	'regex' => '#^/aspirante/dashboard/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'dashboard',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/dashboard/foto/miniatura/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'dashboardFotoMiniatura',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'editar',
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
	'regex' => '#^/aspirante/(\d+)/registrar-pago/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'registrarPago',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/(\d+)/imprimir/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'imprimir',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirante/(\d+)/actualizar/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'actualizar',
);

$ctl_ad[] = array (
	'regex' => '#^/aspirantes/logout/$#',
	'base' => $base,
	'model' => 'Admision_Views_Aspirante',
	'method' => 'cerrar',
);

$ctl_ad[] = array (
	'regex' => '#^/admitir/$#',
	'base' => $base,
	'model' => 'Admision_Views_Admitir',
	'method' => 'index',
);

$ctl_ad[] = array (
	'regex' => '#^/admitir/convocatoria/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Admitir',
	'method' => 'admitir',
);

$ctl_ad[] = array (
	'regex' => '#^/admitir/carrera/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Admitir',
	'method' => 'admitirCarrera',
);

$ctl_ad[] = array (
	'regex' => '#^/admitir/aspirante/(\d+)/$#',
	'base' => $base,
	'model' => 'Admision_Views_Admitir',
	'method' => 'verAspirante',
);

$ctl_ad[] = array (
	'regex' => '#^/admitir/carrera/(\d+)/procesar/$#',
	'base' => $base,
	'model' => 'Admision_Views_Admitir',
	'method' => 'procesar',
);

return $ctl_ad;

<?php
$base = Gatuf::config('pato_base');
$ctl_cp = array ();

/* Bloque base:
$ctl_cp[] = array (
	'regex' => '#^/ /$#',
	'base' => $base,
	'model' => 'CP_Views_',
	'method' => '',
);
*/

$ctl_cp[] = array (
	'regex' => '#^/codigo_postal/JSON/$#',
	'base' => $base,
	'model' => 'CP_Views',
	'method' => 'buscarCPJSON',
);

return $ctl_cp;

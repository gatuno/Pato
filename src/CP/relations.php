<?php

$c = array ();

$c['CP_Municipio'] = array ('relate_to' => array ('CP_Estado'));
$c['CP_CP'] = array ('relate_to' => array ('CP_Asentamiento', 'CP_Municipio', 'CP_Zona'));

return $c;

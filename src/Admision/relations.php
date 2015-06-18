<?php

$m = array ();

$m['Admision_Convocatoria'] = array ('relate_to' => array ('Pato_Calendario'));
$m['Admision_CupoCarrera'] = array ('relate_to' => array ('Pato_Carrera', 'Admision_Convocatoria'));

$m['Admision_Aspirante'] = array ('relate_to' => array ('Admision_CupoCarrera', 'CP_Pais', 'CP_Estado', 'CP_CP', 'CP_Municipio'));

$m['Admision_Estadistica'] = array ('relate_to' => array ('Admision_Aspirante'));

$m['Admision_Documento'] = array ('relate_to_many' => array ('Pato_Alumno'));

return $m;

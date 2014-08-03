<?php

$m = array ();

$m['Pato_Evaluacion'] = array ('relate_to' => array ('Pato_GPE'));
//$m['Pato_Horario'] = array ('relate_to' => array ('Pato_Seccion', 'Pato_Salon'));
$m['Pato_Materia'] = array ('relate_to_many' => array ('Pato_Carrera'));
$m['Pato_Porcentaje'] = array ('relate_to' => array ('Pato_Materia', 'Pato_Evaluacion'));
$m['Pato_Salon'] = array ('relate_to' => array ('Pato_Edificio'));
$m['Pato_Seccion'] = array ('relate_to' => array ('Pato_Materia', 'Pato_Maestro', 'Pato_Carrera'),
                            'relate_to_many' => array ('Pato_Alumno'));
$m['Pato_Boleta'] = array ('relate_to' => array ('Pato_Seccion', 'Pato_Alumno', 'Pato_Evaluacion'));
$m['Pato_Inscripcion'] = array ('relate_to' => array ('Pato_Alumno', 'Pato_Carrera', 'Pato_Calendario', 'Pato_Estatus'));

/* Conexión de señales aquí */
return $m;

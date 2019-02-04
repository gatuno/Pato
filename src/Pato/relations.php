<?php

$m = array ();

$m['Pato_Agenda'] = array ('relate_to' => array ('Pato_Alumno'));
$m['Pato_Asistencia'] = array ('relate_to' => array ('Pato_Seccion', 'Pato_Alumno'));
$m['Pato_Evaluacion'] = array ('relate_to' => array ('Pato_GPE'));
$m['Pato_Horario'] = array ('relate_to' => array ('Pato_Seccion', 'Pato_Salon'));
$m['Pato_Materia'] = array ('relate_to_many' => array ('Pato_Carrera'));
$m['Pato_Porcentaje'] = array ('relate_to' => array ('Pato_Materia', 'Pato_Evaluacion'));
$m['Pato_Salon'] = array ('relate_to' => array ('Pato_Edificio'));
$m['Pato_Seccion'] = array ('relate_to' => array ('Pato_Materia', 'Pato_Maestro', 'Pato_Carrera'),
                            'relate_to_many' => array ('Pato_Alumno'));
$m['Pato_Boleta'] = array ('relate_to' => array ('Pato_Seccion', 'Pato_Alumno', 'Pato_Evaluacion'));
$m['Pato_Inscripcion'] = array ('relate_to' => array ('Pato_Alumno', 'Pato_Carrera', 'Pato_Calendario', 'Pato_Estatus'));
$m['Pato_Kardex'] = array ('relate_to' => array ('Pato_Alumno', 'Pato_Materia', 'Pato_Calendario', 'Pato_GPE'));

$m['Pato_Evaluacion_Respuesta'] = array ('relate_to' => array ('Pato_Alumno', 'Pato_Seccion'));
$m['Pato_Solicitud_Suficiencia'] = array ('relate_to' => array ('Pato_Alumno', 'Pato_Materia', 'Pato_Maestro'));
$m['Pato_PerfilAlumno'] = array ('relate_to' => array ('Pato_Alumno'));

//$m['Pato_Log_Estatus'] = array ('relate_to' => array ('Pato_Alumno', 'Pato_User', 'Pato_Estatus', 'Pato_Calendario'));
$m['Pato_InscripcionEstatus'] = array ('relate_to' => array ('Pato_Inscripcion', 'Pato_Estatus'));
$m['Pato_Log_Kardex'] = array ('relate_to' => array ('Pato_Kardex', 'Pato_Maestro'));

$m['Pato_Planeacion_Unidad'] = array ('relate_to' => array ('Pato_Materia', 'Pato_Maestro'));
$m['Pato_Planeacion_Tema'] = array ('relate_to' => array ('Pato_Planeacion_Unidad'));
$m['Pato_Planeacion_Seguimiento'] = array ('relate_to' => array ('Pato_Planeacion_Tema', 'Pato_Seccion'));

//$m['Pato_Aviso'] = array ('relate_to_many' => array ('Pato_User'));

$m['Pato_MessageA'] = array ('relate_to' => array ('Pato_Alumno'));

/* Conexión de señales aquí */
return $m;

<?php

$m_admin = array ();

$m_admin['Admin_SI_LaboratorioIngreso'] = array ('relate_to' => array ('Pato_Alumno', 'Admin_SI_Laboratorio'));

$m_admin['Admin_Biblioteca_Equipo'] = array ('relate_to' => array ('Admin_Biblioteca'));
$m_admin['Admin_Biblioteca_Prestamo'] = array ('relate_to' => array ('Admin_Biblioteca', 'Pato_User', 'Pato_Maestro', 'Pato_Alumno', 'Pato_Salon', 'Pato_Carrera'), 'relate_to_many' => array ('Admin_Biblioteca_Equipo'));

/* Conexión de señales aquí */
return $m_admin;

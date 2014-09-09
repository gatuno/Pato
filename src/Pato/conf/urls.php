<?php
$base = Gatuf::config('pato_base');
$ctl = array ();

/* Bloque base:
$ctl[] = array (
	'regex' => '#^/ /$#',
	'base' => $base,
	'model' => 'Pato_',
	'method' => '',
);
*/

/* Sistema de login, y vistas base */
$ctl[] = array (
	'regex' => '#^/$#',
	'base' => $base,
	'model' => 'Pato_Views',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/login/$#',
	'base' => $base,
	'model' => 'Pato_Views',
	'method' => 'login',
	'name' => 'login_view'
);

$ctl[] = array (
	'regex' => '#^/logout/$#',
	'base' => $base,
	'model' => 'Pato_Views',
	'method' => 'logout',
);

/* Calendarios */
$ctl[] = array (
	'regex' => '#^/calendario/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'cambiarCalendario',
);

$ctl[] = array (
	'regex' => '#^/calendarios/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/calendarios/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'agregarCalendario',
);

/* Recuperación de contraseñas */
$ctl[] = array (
	'regex' => '#^/password/$#',
	'base' => $base,
	'model' => 'Pato_Views',
	'method' => 'passwordRecoveryAsk',
);

$ctl[] = array (
	'regex' => '#^/password/ik/$#',
	'base' => $base,
	'model' => 'Pato_Views',
	'method' => 'passwordRecoveryInputCode',
);

$ctl[] = array (
	'regex' => '#^/password/k/(.*)/$#',
	'base' => $base,
	'model' => 'Pato_Views',
	'method' => 'passwordRecovery',
);

$ctl[] = array(
	'regex' => '#^/password/change/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'passwordChange',
);

$ctl[] = array(
	'regex' => '#^/password/reset/(.*)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'passwordReset',
);

/* Las carreras */
$ctl[] = array(
	'regex' => '#^/carreras/$#',
	'base' => $base,
	'model' => 'Pato_Views_Carrera',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/carreras/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Carrera',
	'method' => 'agregarCarrera',
);

$ctl[] = array(
	'regex' => '#^/carrera/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Carrera',
	'method' => 'verCarrera',
);

$ctl[] = array(
	'regex' => '#^/carrera/([A-Za-z]{2,5})/update/$#',
	'base' => $base,
	'model' => 'Pato_Views_Carrera',
	'method' => 'actualizarCarrera',
);

/* Los alumnos */
$ctl[] = array(
	'regex' => '#^/alumnos/$#',
	'base' => $base,
	'model' => 'Pato_Views_Alumno',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/alumnos/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Alumno',
	'method' => 'agregarAlumno',
);

$ctl[] = array(
	'regex' => '#^/alumnos/buscar/JSON/$#',
	'base' => $base,
	'model' => 'Pato_Views_Alumno',
	'method' => 'buscarJSON',
);

/*$ctl[] = array(
	'regex' => '#^/alumnos/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Alumno',
	'method' => 'porCarrera',
);*/

$ctl[] = array(
	'regex' => '#^/alumno/#',
	'base' => $base,
	'sub' => array (
		array(
			'regex' => '#^(\w\d{7})/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verAlumno',
		),
		array(
			'regex' => '#^(\w\d{7})/grupos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verGrupos',
		),
		array(
			'regex' => '#^(\w\d{7})/horario/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verHorario',
		),
		array(
			'regex' => '#^(\w\d{7})/update/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'actualizarAlumno',
		),
		array (
			'regex' => '#^(\w\d{7})/inscripciones/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verInscripciones',
		),
		array (
			'regex' => '#^(\w\d{7})/formatos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verFormatos',
		),
		array (
			'regex' => '#^(\w\d{7})/formato/boleta/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'boleta',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'agenda',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/registro/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'registro',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/eliminar/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'registroEliminar',
		),
	)
);

/* Algunas materias */
$ctl[] = array (
	'regex' => '#^/materias/$#',
	'base' => $base,
	'model' => 'Pato_Views_Materia',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/materias/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Materia',
	'method' => 'agregarMateria',
);

$ctl[] = array (
	'regex' => '#^/materias/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Materia',
	'method' => 'porCarrera',
);

$ctl[] = array (
	'regex' => '#^/materias/nofiltro/([c])/$#',
	'base' => $base,
	'model' => 'Pato_Views_Materia',
	'method' => 'eliminarFiltro',
);

$ctl[] = array (
	'regex' => '#^/materia/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^([\w-]+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'verMateria',
		),
		array (
			'regex' => '#^([\w-]+)/update/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'actualizarMateria',
		),
		array (
			'regex' => '#^([\w-]+)/horas/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'verHoras',
		),
		array (
			'regex' => '#^([\w-]+)/addcarrera/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'agregarACarrera',
		),
		array (
			'regex' => '#^([\w-]+)/delcarrera/([A-Za-z]{2,5})/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'eliminarDeCarrera',
		),
		array (
			'regex' => '#^([\w-]+)/evals/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'verEvals',
		),
		array (
			'regex' => '#^([\w-]+)/evals/add/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'agregarEval',
		),
		array (
			'regex' => '#^([\w-]+)/evals/update/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'editarEval',
		),
		array (
			'regex' => '#^([\w-]+)/evals/delete/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Materia',
			'method' => 'eliminarEval',
		),
	)
);

/* Las secciones */
$ctl[] = array (
	'regex' => '#^/secciones/$#',
	'base' => $base,
	'model' => 'Pato_Views_Seccion',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/secciones/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Seccion',
	'method' => 'agregarNrc',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/c/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porCarrera',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/n/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porNoAsignadas',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/a/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porAsignadas',
);

$ctl[] = array (
	'regex' => '#^/secciones/f/s/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'porSuplente',
);

$ctl[] = array (
	'regex' => '#^/secciones/nofiltro/([acns])/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'eliminarFiltro',
);

$ctl[] = array (
	'regex' => '#^/seccion/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'verNrc',
		),
		array (
			'regex' => '#^(\d+)/update/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'actualizarNrc',
		),
		array (
			'regex' => '#^(\d+)/delete/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'eliminarNrc',
		),
		array (
			'regex' => '#^(\d+)/alumnos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'verAlumnos',
		),
		array (
			'regex' => '#^(\d+)/formatos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'verFormatos',
		),
		array (
			'regex' => '#^(\d+)/formato/acta_calif/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'actaCalificaciones',
		),
		array (
			'regex' => '#^(\d+)/timeadd/$#',
			'base' => $base,
			'model' => 'Pato_Views_Horario',
			'method' => 'agregarHora',
		),
		array (
			'regex' => '#^(\d+)/timedel/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Horario',
			'method' => 'eliminarHora',
		),
		array (
			'regex' => '#^(\d+)/timeupdate/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Horario',
			'method' => 'actualizarHora',
		),
		array (
			'regex' => '#^(\d+)/reclamar/([A-Za-z]{2,5})/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'reclamarNrc',
		),
		array (
			'regex' => '#^(\d+)/liberar/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'liberarNrc',
		),
		array (
			'regex' => '#^(\d+)/matricular/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'matricular',
		),
		array (
			'regex' => '#^(\d+)/desmatricular/(\w\d{7})/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'desmatricular',
		),
		array (
			'regex' => '#^(\d+)/evaluar/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'evaluar',
		),
		array (
			'regex' => '#^(\d+)/asistencias/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'evaluarAsistencias',
		),
	)
);

/* Los salones */
$ctl[] = array (
	'regex' => '#^/salon/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Pato_Views_Salon',
	'method' => 'actualizarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/add/(.+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Salon',
	'method' => 'agregarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/buscar/$#',
	'base' => $base,
	'model' => 'Pato_Views_Salon',
	'method' => 'buscarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/buscar/reporte/$#',
	'base' => $base,
	'model' => 'Pato_Views_Salon',
	'method' => 'reporteBuscados',
);

/* Los profesores */
$ctl[] = array (
	'regex' => '#^/profesores/$#',
	'base' => $base,
	'model' => 'Pato_Views_Maestro',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/profesores/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Maestro',
	'method' => 'agregarMaestro',
);

$ctl[] = array(
	'regex' => '#^/profesores/buscar/JSON/$#',
	'base' => $base,
	'model' => 'Pato_Views_Maestro',
	'method' => 'buscarJSON',
);

$ctl[] = array (
	'regex' => '#^/profesor/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'verMaestro',
		),
		array (
			'regex' => '#^(\d+)/update/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'actualizarMaestro',
		),
		array (
			'regex' => '#^(\d+)/horario/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'verHorario',
		),
		array (
			'regex' => '#^(\d+)/horario/PDF/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'verHorarioPDF',
		),
		array (
			'regex' => '#^(\d+)/permisos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'permisos',
		)
	)
);

/* Edificios */
$ctl[] = array (
	'regex' => '#^/edificios/$#',
	'base' => $base,
	'model' => 'Pato_Views_Edificio',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/edificio/(.+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Edificio',
	'method' => 'verEdificio',
);

$ctl[] = array (
	'regex' => '#^/edificios/add/$#',
	'base' => $base,
	'model' => 'Pato_Views_Edificio',
	'method' => 'agregarEdificio',
);

/* usuarios*/
$ctl[] = array(
	'regex' => '#^/permisos/add/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'agregarPermiso',
);

$ctl[] = array(
	'regex' => '#^/permisos/del/(\d+)/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'eliminarPermiso',
);

$ctl[] = array(
	'regex' => '#^/grupos/del/(\d+)/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'eliminarGrupo',
);

$ctl[] = array(
	'regex' => '#^/permisos/addGrupo/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'agregarGrupo',
);

$ctl[] = array (
	'regex' => '#^/preferencias/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/preferencias/folio/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias',
	'method' => 'cambiarFolio',
);

$ctl[] = array (
	'regex' => '#^/utils/$#',
	'base' => $base,
	'model' => 'Pato_Views_Utils',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/utils/boletas-lote/$#',
	'base' => $base,
	'model' => 'Pato_Views_Utils',
	'method' => 'loteBoletas',
);

$ctl[] = array (
	'regex' => '#^/utils/boletas-lote/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Utils',
	'method' => 'loteBoletaCarrera',
);

$ctl[] = array (
	'regex' => '#^/calificaciones/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calificaciones',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/calificaciones/a-kardex/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calificaciones',
	'method' => 'aKardex',
);

$ctl[] = array(
	'regex' => '#^/test/$#',
	'base' => $base,
	'model' => 'Pato_Views_Test',
	'method' => 'test',
);

return $ctl;

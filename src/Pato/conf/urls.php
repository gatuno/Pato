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

/* Reportes y acciones varias */
$ctl[] = array (
	'regex' => '#^/sistema/$#',
	'base' => $base,
	'model' => 'Calif_Views_System',
	'method' => 'index',
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
			'regex' => '#^(\d{8})/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verAlumno',
		),
		array(
			'regex' => '#^(\d{8})/grupos/$#',
			'base' => $base,
			'model' => 'Calif_Views_Alumno',
			'method' => 'verGruposAlumno',
		),
		array(
			'regex' => '#^(\d{8})/update/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'actualizarAlumno',
		),
		array (
			'regex' => '#^(\d{8})/inscripciones/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verInscripciones',
		),
		/*array ( FIXME: Mal acomodada
			'regex' => '#^(\d{8})/evaluar/(\d+)/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Alumno',
			'method' => 'evaluar',
		)*/
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
			'regex' => '#^([\w-]+)/evals/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'verEval',
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
			'regex' => '#^([\w-]+)/addeval/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Materia',
			'method' => 'agregarEval',
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
		)
	)
);

/* Las evaluaciones */
$ctl[] = array (
	'regex' => '#^/evaluaciones/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/evaluaciones/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'agregarEval',
);

$ctl[] = array (
	'regex' => '#^/evaluacion/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'verEval',
);

$ctl[] = array (
	'regex' => '#^/evaluacion/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Evaluacion',
	'method' => 'actualizarEval',
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
	'regex' => '#^/secciones/error/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Seccion',
	'method' => 'errorHoras',
);

$ctl[] = array (
	'regex' => '#^/secciones/reporte/ODS/$#',
	'base' => $base,
	'model' => 'Calif_Views_Reportes_Oferta',
	'method' => 'reporteODS',
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
			'regex' => '#^(\d+)/timeadd/$#',
			'base' => $base,
			'model' => 'Calif_Views_Horario',
			'method' => 'agregarHora',
		),
		array (
			'regex' => '#^(\d+)/timedel/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Horario',
			'method' => 'eliminarHora',
		),
		array (
			'regex' => '#^(\d+)/timeupdate/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Horario',
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
			'regex' => '#^(\d+)/evaluar/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'evaluar'
		),
		array (
			'regex' => '#^(\d+)/matricular/$#',
			'base' => $base,
			'model' => 'Calif_Views_Seccion',
			'method' => 'matricular'
		)
	)
);

/* Los salones */
$ctl[] = array (
	'regex' => '#^/salon/(\d+)/update/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'actualizarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/add/(.+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'agregarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/buscar/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
	'method' => 'buscarSalon',
);

$ctl[] = array (
	'regex' => '#^/salones/buscar/reporte/$#',
	'base' => $base,
	'model' => 'Calif_Views_Salon',
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
	'model' => 'Calif_Views_Edificio',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/edificio/(.+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Edificio',
	'method' => 'verEdificio',
);

$ctl[] = array (
	'regex' => '#^/edificios/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Edificio',
	'method' => 'agregarEdificio',
);

/* Departamentos */
$ctl[] = array (
	'regex' => '#^/departamentos/$#',
	'base' => $base,
	'model' => 'Calif_Views_Departamento',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/departamentos/buscarerror/$#',
	'base' => $base,
	'model' => 'Calif_Views_Departamento',
	'method' => 'buscarErrorHoras',
);

$ctl[] = array (
	'regex' => '#^/departamentos/add/$#',
	'base' => $base,
	'model' => 'Calif_Views_Departamento',
	'method' => 'agregarDepartamento',
);

/* Número de Puestos */
$ctl[] = array (
	'regex' => '#^/puesto/(\d+)/$#',
	'base' => $base,
	'model' => 'Calif_Views_Puestos',
	'method' => 'actualizarPuesto',
);

$ctl[] = array (
	'regex' => '#^/reportes/$#',
	'base' => $base,
	'model' => 'Calif_Views_Reportes',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/reporte/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^profesores/carga/$#',
			'base' => $base,
			'model' => 'Calif_Views_Reportes_Maestro',
			'method' => 'cargaHoraria',
		),
		array (
			'regex' => '#^profesores/carga/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Reportes_Maestro',
			'method' => 'cargaHorariaPorDepartamento',
		),
		array (
			'regex' => '#^profesores/suplencias/$#',
			'base' => $base,
			'model' => 'Calif_Views_Reportes_Maestro',
			'method' => 'suplencias',
		),
		array (
			'regex' => '#^profesores/suplencias/(\d+)/$#',
			'base' => $base,
			'model' => 'Calif_Views_Reportes_Maestro',
			'method' => 'suplenciasPorDepartamento',
		),
	)
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

$ctl[] = array(
	'regex' => '#^/test/$#',
	'base' => $base,
	'model' => 'Pato_Views_Test',
	'method' => 'test',
);

return $ctl;

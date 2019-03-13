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
	'model' => 'Pato_Views_Index',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/login/$#',
	'base' => $base,
	'model' => 'Pato_Views_Login',
	'method' => 'login',
	'name' => 'login_view'
);

$ctl[] = array (
	'regex' => '#^/logout/$#',
	'base' => $base,
	'model' => 'Pato_Views_Login',
	'method' => 'logout',
);

$ctl[] = array (
	'regex' => '#^/aviso/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Aviso',
	'method' => 'leer',
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

$ctl[] = array (
	'regex' => '#^/calendarios/(\w+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'ver',
);

$ctl[] = array (
	'regex' => '#^/calendarios/(\w+)/configurar/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'configurar',
);

$ctl[] = array (
	'regex' => '#^/calendarios/(\w+)/actual/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'cambiarActual',
);

$ctl[] = array (
	'regex' => '#^/calendarios/(\w+)/siguiente/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calendario',
	'method' => 'cambiarSiguiente',
);

$ctl[] = array (
	'regex' => '#^/calendarios/(\w+)/festivo/agregar/$#',
	'base' => $base,
	'model' => 'Pato_Views_DiaFestivo',
	'method' => 'agregar',
);

/* Recuperación de contraseñas */
$ctl[] = array (
	'regex' => '#^/password/recovery/$#',
	'base' => $base,
	'model' => 'Pato_Views_Login',
	'method' => 'passwordRecoveryAsk',
);

$ctl[] = array (
	'regex' => '#^/password/recovery/done/$#',
	'base' => $base,
	'model' => 'Pato_Views_Login',
	'method' => 'passwordRecoverWait',
);

$ctl[] = array (
	'regex' => '#^/password/ik/$#',
	'base' => $base,
	'model' => 'Pato_Views_Login',
	'method' => 'passwordRecoveryInputCode',
);

$ctl[] = array (
	'regex' => '#^/password/k/(.*)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Login',
	'method' => 'passwordRecovery',
);

$ctl[] = array(
	'regex' => '#^/password/change/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'passwordChange',
);

/* FIXME: */
$ctl[] = array (
	'regex' => '#^/mail/change/$#',
	'base' => $base,
	'model' => 'Pato_Views_Usuario',
	'method' => 'emailChange',
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
			'method' => 'verPerfil',
		),
		array(
			'regex' => '#^(\w\d{7})/calificaciones/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verCalificaciones',
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
			'regex' => '#^(\w\d{7})/formatos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'verFormatos',
		),
		array (
			'regex' => '#^(\w\d{7})/formato/boleta/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'boleta',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/$#',
			'base' => $base,
			'model' => 'Pato_Views_Agenda',
			'method' => 'agenda',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/nueva/$#',
			'base' => $base,
			'model' => 'Pato_Views_Agenda',
			'method' => 'crearAgenda',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/cambiar/$#',
			'base' => $base,
			'model' => 'Pato_Views_Agenda',
			'method' => 'cambiarAgenda',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/eliminar/$#',
			'base' => $base,
			'model' => 'Pato_Views_Agenda',
			'method' => 'eliminarAgenda',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/registro/$#',
			'base' => $base,
			'model' => 'Pato_Views_Agenda',
			'method' => 'registro',
		),
		array (
			'regex' => '#^(\w\d{7})/agenda/eliminar/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Agenda',
			'method' => 'registroEliminar',
		),
		array (
			'regex' => '#^(\w\d{7})/evaluacion/profesores/$#',
			'base' => $base,
			'model' => 'Pato_Views_Evaluacion_Profesor',
			'method' => 'listar_evals',
		),
		array (
			'regex' => '#^evaluar/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Evaluacion_Profesor',
			'method' => 'evaluar',
		),
		array (
			'regex' => '#^(\w\d{7})/kardex/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'kardex',
		),
		array (
			'regex' => '#^(\w\d{7})/kardex/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'kardexCarrera',
		),
		array (
			'regex' => '#^(\w\d{7})/perfil/update/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'editarPerfil',
		),
		array (
			'regex' => '#^(\w\d{7})/password/reset/$#',
			'base' => $base,
			'model' => 'Pato_Views_Alumno',
			'method' => 'passwordReset'
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
			'regex' => '#^(\d+)/operaciones/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'verOperaciones',
		),
		array (
			'regex' => '#^(\d+)/formato/acta_calif/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'actaCalificaciones',
		),
		array (
			'regex' => '#^(\d+)/formato/lista_asistencias/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'listaAsistencia',
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
		array (
			'regex' => '#^(\d+)/cerrarAKardex/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Seccion',
			'method' => 'cerrarAKardex',
		),
	)
);

/* Planeación */
$ctl[] = array (
	'regex' => '#^/planeacion/$#',
	'base' => $base,
	'model' => 'Pato_Views_Planeacion',
	'method' => 'index',
	'params' => 'myself',
	'name' => 'planeacion_propia',
);

$ctl[] = array (
	'regex' => '#^/planeacion/reportes/$#',
	'base' => $base,
	'model' => 'Pato_Views_Planeacion_Reportes',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/planeacion/reportes/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^profesor/(\d+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion_Reportes',
			'method' => 'reportePorMaestro',
		),
		array (
			'regex' => '#^materia/([\w-]+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion_Reportes',
			'method' => 'reportePorMateria',
		),
	)
);

$ctl[] = array (
	'regex' => '#^/planeacion/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^materia/([\w-]+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'verMateria',
			'params' => 'myself',
			'name' => 'planeacion_materia_propia',
		),
		array (
			'regex' => '#^(\d+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'index',
			'params' => 'other',
			'name' => 'planeacion_otros',
		),
		array (
			'regex' => '#^(\d+)/materia/([\w-]+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'verMateria',
			'params' => 'other',
			'name' => 'planeacion_materia_otros',
		),
		array (
			'regex' => '#^agregar/unidad/([\w-]+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'agregarUnidad',
		),
		array (
			'regex' => '#^seleccionar/unidad/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'seleccionarUnidad',
		),
		array (
			'regex' => '#^agregar/tema/(\d+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'agregarTema',
		),
		array (
			'regex' => '#^eliminar/tema/(\d+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'borrarTema',
		),
		array (
			'regex' => '#^seguimiento/(\d+)/nrc/(\d+)/$#',
			'base' => '',
			'model' => 'Pato_Views_Planeacion',
			'method' => 'seguimiento',
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
	'regex' => '#^/salones/add/$#',
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
		),
		array (
			'regex' => '#^(\d+)/password/reset/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'passwordReset',
		),
		array (
			'regex' => '#^(\d+)/permisos/add/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'agregarPermiso',
		),
		array (
			'regex' => '#^(\d+)/permisos/del/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'eliminarPermiso',
		),
		array (
			'regex' => '#^(\d+)/grupos/add/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'agregarGrupo',
		),
		array (
			'regex' => '#^(\d+)/grupos/del/$#',
			'base' => $base,
			'model' => 'Pato_Views_Maestro',
			'method' => 'eliminarGrupo',
		),
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
$ctl[] = array (
	'regex' => '#^/preferencias/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/foliador/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias', /* TODO: Mover el foliador a su propia vista */
	'method' => 'cambiarFolio',
);

$ctl[] = array (
	'regex' => '#^/foliador/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^(\d+)/eliminar/$#',
			'base' => $base,
			'model' => 'Pato_Views_Preferencias', /* TODO: Mover el foliador a su propia vista */
			'method' => 'eliminarFolio',
		),
		array (
			'regex' => '#^subir/$#',
			'base' => $base,
			'model' => 'Pato_Views_Preferencias', /* TODO: Mover el foliador a su propia vista */
			'method' => 'subirFolios',
		),
	)
);

$ctl[] = array (
	'regex' => '#^/preferencias/fecha/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias',
	'method' => 'cambiarFecha',
);

$ctl[] = array (
	'regex' => '#^/preferencias/terminos-suficiencias/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias',
	'method' => 'terminosSuficiencias',
);

$ctl[] = array (
	'regex' => '#^/preferencias/evaluacion_profesores/$#',
	'base' => $base,
	'model' => 'Pato_Views_Preferencias',
	'method' => 'evalProf',
);

$ctl[] = array (
	'regex' => '#^/utils/$#',
	'base' => $base,
	'model' => 'Pato_Views_Utils',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/utils/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^boletas-lote/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'loteBoletas',
		),
		array (
			'regex' => '#^boletas-lote/([A-Za-z]{2,5})/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'loteBoletaCarrera',
		),
		array (
			'regex' => '#^altas-bajas/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'altasBajasMasivas',
		),
		array (
			'regex' => '#^cambiar-fechas-eval/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'cambiarFechaEval',
		),
		array (
			'regex' => '#^agregar-eval/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'agregarPorcentaje',
		),
		array (
			'regex' => '#^actualizar-porcentaje/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'cambiarPorcentaje',
		),
		array (
			'regex' => '#^generar-agendas/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'generarAgendas',
		),
		array (
			'regex' => '#^agregar-postal/$#',
			'base' => $base,
			'model' => 'Pato_Views_Utils',
			'method' => 'agregarPostal',
		),
	)
);

$ctl[] = array (
	'regex' => '#^/calificaciones/$#',
	'base' => $base,
	'model' => 'Pato_Views_Calificaciones',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/calificaciones/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^a-kardex/$#',
			'base' => $base,
			'model' => 'Pato_Views_Calificaciones',
			'method' => 'aKardex',
		),
		array (
			'regex' => '#^a-kardex/selectivo/$#',
			'base' => $base,
			'model' => 'Pato_Views_Calificaciones',
			'method' => 'aKardexSelectivo',
		),
		array (
			'regex' => '#^nueva-kardex/$#',
			'base' => $base,
			'model' => 'Pato_Views_Calificaciones',
			'method' => 'levantarKardex',
		),
		array (
			'regex' => '#^correccion/$#',
			'base' => $base,
			'model' => 'Pato_Views_Calificaciones',
			'method' => 'correccionBuscar',
		),
		array (
			'regex' => '#^correccion/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Calificaciones',
			'method' => 'correccionKardex',
		),
	)
);

$ctl[] = array (
	'regex' => '#^/reportes/$#',
	'base' => $base,
	'model' => 'Pato_Views_Reportes',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/reportes/#',
	'base' => $base,
	'sub' => array (
		array (
			'regex' => '#^oferta/matriculados/calendario/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'matriculadosCalendario',
		),
		array (
			'regex' => '#^oferta/matriculados/calendario/ODS/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'matriculadosCalendarioODS',
		),
		array (
			'regex' => '#^oferta/materia/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'matriculadosMateriaIndex',
		),
		array (
			'regex' => '#^oferta/materia/([\w-]+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'matriculadosMateria',
		),
		array (
			'regex' => '#^oferta/materia/([\w-]+)/ODS/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'matriculadosMateriaODS',
		),
		array (
			'regex' => '#^oferta/materias/ODS/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'matriculadosMateriaTodosODS',
		),
		array (
			'regex' => '#^oferta/maestros-activos/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'maestrosActivos',
		),
		array (
			'regex' => '#^oferta/maestros-activos/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Oferta',
			'method' => 'maestrosActivosCalendario',
		),
		array (
			'regex' => '#^calificaciones/subida-tarde/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Calificaciones',
			'method' => 'subidaTarde',
		),
		array (
			'regex' => '#^calificaciones/subida-tarde/(\d+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Calificaciones',
			'method' => 'subidaTardeReporte',
		),
		array (
			'regex' => '#^calificaciones/reprobacion/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Calificaciones',
			'method' => 'indiceReprobacion',
		),
		array (
			'regex' => '#^calificaciones/reprobacion/ODS/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Calificaciones',
			'method' => 'indiceReprobacionODS',
		),
		array (
			'regex' => '#^calificaciones/promedio-carrera/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Calificaciones',
			'method' => 'promedioCarrera',
		),
		array (
			'regex' => '#^alumnos/ingreso/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Alumnos',
			'method' => 'ingreso',
		),
		array (
			'regex' => '#^alumnos/ingreso/(\w+)/$#',
			'base' => $base,
			'model' => 'Pato_Views_Reportes_Alumnos',
			'method' => 'ingresoReporte',
		),
	)
);

$ctl[] = array (
	'regex' => '#^/estatus/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/estatus/licencia/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'licenciaSeleccionar',
);

$ctl[] = array (
	'regex' => '#^/estatus/licencia/(\w\d{7})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'licenciaEjecutar',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-voluntaria/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaVoluntariaSeleccionar',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-voluntaria/(\w\d{7})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaVoluntariaEjecutar',
);

$ctl[] = array (
	'regex' => '#^/estatus/cambio-carrera/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'cambioCarrera',
);

$ctl[] = array (
	'regex' => '#^/estatus/cambio-carrera/(\w\d{7})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'cambioCarreraAlumno',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-academica/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaAcademica',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-administrativa/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaAdministrativaSeleccionar',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-administrativa/(\w\d{7})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaAdministrativaAlumno',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-administrativa/regresar/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaAdministrativaRegresarSeleccionar',
);

$ctl[] = array (
	'regex' => '#^/estatus/baja-administrativa/regresar/(\w\d{7})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'bajaAdministrativaRegresar',
);

$ctl[] = array (
	'regex' => '#^/estatus/documentos/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'recibirDocumentosSeleccionar',
);

$ctl[] = array (
	'regex' => '#^/estatus/documentos/(\w\d{7})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'recibirDocumentos',
);

$ctl[] = array (
	'regex' => '#^/estatus/documentos/(\w\d{7})/reporte/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'recibirDocumentosReporte',
);

$ctl[] = array (
	'regex' => '#^/estatus/documentos/(\w\d{7})/reporte/PDF/$#',
	'base' => $base,
	'model' => 'Pato_Views_Estatus',
	'method' => 'recibirDocumentosReportePDF',
);

$ctl[] = array (
	'regex' => '#^/evaluacion/profesores/$#',
	'base' => $base,
	'model' => 'Pato_Views_Evaluacion_Profesor',
	'method' => 'index',
);

$ctl[] = array (
	'regex' => '#^/evaluacion/profesores/resultados/$#',
	'base' => $base,
	'model' => 'Pato_Views_Evaluacion_Profesor',
	'method' => 'resultados',
);

$ctl[] = array (
	'regex' => '#^/evaluacion/profesores/resultados/(\d+)/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Evaluacion_Profesor',
	'method' => 'resultadoMaestro',
);

$ctl[] = array(
	'regex' => '#^/suficiencias/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'index',
);

$ctl[] = array(
	'regex' => '#^/suficiencias/ver/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'solicitudes',
);

$ctl[] = array(
	'regex' => '#^/suficiencias/nueva/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'nueva',
);

$ctl[] = array(
	'regex' => '#^/suficiencias/eliminar/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'eliminar',
);

$ctl[] = array(
	'regex' => '#^/suficiencias/actualizar/(\d+)/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'actualizar',
);

$ctl[] = array (
	'regex' => '#^/suficiencias/revisar/([A-Za-z]{2,5})/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'revisarCarrera',
);

$ctl[] = array (
	'regex' => '#^/suficiencias/revisar/([A-Za-z]{2,5})/aprobar/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'aprobarCarrera',
);

$ctl[] = array(
	'regex' => '#^/suficiencias/crearNRCs/$#',
	'base' => $base,
	'model' => 'Pato_Views_Solicitud_Suficiencias',
	'method' => 'crearNRCs',
);

$ctl[] = array (
	'regex' => '#^/admin#',
	'base' => $base,
	'sub' => include 'Admin/conf/urls.php',
);

$ctl[] = array (
	'regex' => '#^/admision#',
	'base' => $base,
	'sub' => include 'Admision/conf/urls.php',
);

$ctl[] = array (
	'regex' => '#^/sepomex#',
	'base' => $base,
	'sub' => include 'CP/conf/urls.php',
);

$ctl[] = array(
	'regex' => '#^/test/$#',
	'base' => $base,
	'model' => 'Pato_Views_Test',
	'method' => 'test',
);

return $ctl;

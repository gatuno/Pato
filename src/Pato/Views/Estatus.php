<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Estatus {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/index.html',
		                                         array('page_title' => 'Administración de estatus del Alumno'),
                                                 $request);
	}
	
	public $licenciaSeleccionar_precond = array ('Gatuf_Precondition::adminRequired');
	public function licenciaSeleccionar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				/* Redirigir a la confirmación */
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::licenciaEjecutar', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/licencia-seleccionar.html',
		                                         array('page_title' => 'Aplicar licencia',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $licenciaEjecutar_precond = array ('Gatuf_Precondition::adminRequired');
	public function licenciaEjecutar ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::licenciaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Revisar el estatus del alumno, sólo se puede aplicar licencia si está activo */
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins == null) {
			return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/licencia-inactivo.html',
			                                         array('page_title' => 'Aplicar licencia',
			                                               'alumno' => $alumno),
	                                                 $request);
		}
		
		$estatus = $ins->get_estatus ();
		if ($estatus->clave == 'LI') {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' ya tiene una licencia activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::licenciaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			/* La confirmación de regreso
			 *
			 * Como es una licencia, hay que eliminar todas las materias en las que esté matriculado
			 * Incluye las calificaciones en boleta y asistencias, como si nunca hubiera existido
			 */
			
			$secciones = $alumno->get_grupos_list ();
			
			foreach ($secciones as $seccion) {
				/* TODO: Convertir esto en un TRIGGER */
				$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
				
				$asistencias = Gatuf::factory ('Pato_Asistencia')->getList (array ('filter' => $sql->gen ()));
				foreach ($asistencias as $asis) {
					$asis->delete ();
				}
				
				$boletas = Gatuf::factory ('Pato_Boleta')->getList (array ('filter' => $sql->gen ()));
				foreach ($boletas as $b) {
					$b->delete ();
				}
				
				$seccion->delAssoc ($alumno);
			}
			
			$gsettings = new Gatuf_GSetting ();
			$gsettings->setApp ('Patricia');
		
			$cal = $gsettings->getVal ('calendario_activo', null);
		
			$calendario_actual = new Pato_Calendario ($cal);
			
			/* Registrar los cambios de estatus */
			$log = new Pato_Log_Estatus ();
			$log->alumno = $alumno;
			$log->viejo = $ins->get_estatus ();
			$log->usuario = $request->user;
			$log->calendario = $calendario_actual;
			
			$estatus = new Pato_Estatus ('LI');
			$log->nuevo = $ins->estatus = $estatus;
			$ins->update ();
			
			$log->create ();
			
			$request->user->setMessage (1, 'El alumno '.((string) $alumno).' está de licencia');
			/* Redirigir al estatus del alumno */
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::kardex', $alumno->codigo);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$car = $ins->get_carrera ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/licencia-aplicar.html',
		                                         array('page_title' => 'Aplicar licencia',
		                                               'alumno' => $alumno,
		                                               'estatus' => $estatus,
		                                               'inscripcion' => $ins,
		                                               'carrera' => $car),
		                                         $request);
	}
	
	public $bajaVoluntariaSeleccionar_precond = array ('Gatuf_Precondition::adminRequired');
	public function bajaVoluntariaSeleccionar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				/* Redirigir a la confirmación */
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaVoluntariaEjecutar', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-voluntaria-seleccionar.html',
		                                         array('page_title' => 'Baja voluntaria',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $bajaVoluntariaEjecutar_precond = array ('Gatuf_Precondition::adminRequired');
	public function bajaVoluntariaEjecutar ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaVoluntariaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Revisar el estatus del alumno, sólo se puede aplicar licencia si está activo */
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins == null) {
			return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-voluntaria-inactivo.html',
			                                         array('page_title' => 'Aplicar baja voluntaria',
			                                               'alumno' => $alumno),
	                                                 $request);
		}
		
		$estatus = $ins->get_estatus ();
		if ($estatus->clave == 'BV') {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' ya está de baja voluntaria');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::licenciaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		
		$cal = $gsettings->getVal ('calendario_activo', null);
		
		$calendario_actual = new Pato_Calendario ($cal);
		
		if ($request->method == 'POST') {
			/* La confirmación de regreso
			 *
			 * Como es una baja voluntaria, hay que eliminar todas las materias en las que esté matriculado
			 * Incluye las calificaciones en boleta y asistencias, como si nunca hubiera existido
			 */
			
			$secciones = $alumno->get_grupos_list ();
			
			foreach ($secciones as $seccion) {
				/* TODO: Convertir esto en un TRIGGER */
				$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
				
				$asistencias = Gatuf::factory ('Pato_Asistencia')->getList (array ('filter' => $sql->gen ()));
				foreach ($asistencias as $asis) {
					$asis->delete ();
				}
				
				$boletas = Gatuf::factory ('Pato_Boleta')->getList (array ('filter' => $sql->gen ()));
				foreach ($boletas as $b) {
					$b->delete ();
				}
				
				$seccion->delAssoc ($alumno);
			}
			
			/* Registrar los cambios de estatus */
			$log = new Pato_Log_Estatus ();
			$log->alumno = $alumno;
			$log->viejo = $ins->get_estatus ();
			$log->usuario = $request->user;
			$log->calendario = $calendario_actual;
			
			$estatus = new Pato_Estatus ('BV');
			$log->nuevo = $ins->estatus = $estatus;
			
			$ins->egreso = $calendario_actual;
			$ins->update ();
			
			$log->create ();
			$request->user->setMessage (1, 'El alumno '.((string) $alumno).' está de baja voluntaria.');
			
			/* Redirigir al estatus del alumno */
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::kardex', $alumno->codigo);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$car = $ins->get_carrera ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-voluntaria-aplicar.html',
		                                         array('page_title' => 'Aplicar baja voluntaria',
		                                               'alumno' => $alumno,
		                                               'estatus' => $estatus,
		                                               'inscripcion' => $ins,
		                                               'calendario_actual' => $calendario_actual,
		                                               'carrera' => $car),
		                                         $request);
	}
	
	public $cambioCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambioCarrera ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::cambioCarreraAlumno', $alumno->codigo);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/cambio-carrera-seleccionar.html',
		                                         array('page_title' => 'Cambiar carrera',
                                                       'form' => $form),
                                                 $request);
	}
	
	public $cambioCarreraAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambioCarreraAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins === null) {
			/* No está activo, no podemos moverlo de carrera */
			$request->user->setMessage (3, 'El alumno seleccionado no tiene ninguna carrera activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::cambioCarrera');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Estatus_CambioCarrera ($request->POST);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$egreso = new Pato_Calendario ($data['egreso']);
				/* Registrar los cambios de estatus */
				$log = new Pato_Log_Estatus ();
				$log->alumno = $alumno;
				$log->viejo = $ins->get_estatus ();
				$log->usuario = $request->user;
				$log->calendario = $egreso;
				$estatus = new Pato_Estatus ('CC'); /* Cambio de carrera */
				
				$ins->egreso = $egreso;
				$log->nuevo = $ins->estatus = $estatus;
				
				$ins->update ();
				
				$log->create ();
				
				$nueva = new Pato_Carrera ($data['carrera']);
				
				$gconf = new Gatuf_GSetting ();
				$gconf->setApp ('Patricia');
				$ingreso = new Pato_Calendario ($gconf->getVal ('calendario_activo', null));
				$estatus = new Pato_Estatus ('AC'); /* Activo */
				
				$inscripcion = new Pato_Inscripcion ();
				$inscripcion->alumno = $alumno;
				$inscripcion->ingreso = $ingreso;
				$inscripcion->carrera = $nueva;
				$inscripcion->turno = $data['turno'];
				$inscripcion->estatus = $estatus;
				
				$inscripcion->create ();
				
				$request->user->setMessage (1, 'El alumno ha sido movido satisfactoriamente a la nueva carrera');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::cambioCarrera');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Estatus_CambioCarrera (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/cambio-carrera.html',
		                                         array('page_title' => 'Cambiar carrera',
                                                       'form' => $form,
                                                       'alumno' => $alumno,
                                                       'inscripcion' => $ins),
                                                 $request);
	}
}

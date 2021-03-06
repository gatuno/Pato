<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Estatus {
	public $index_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/index.html',
		                                         array('page_title' => 'Administración de estatus del Alumno'),
		                                         $request);
	}
	
	public $licenciaSeleccionar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
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
	
	public $licenciaEjecutar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
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
		
		$estatus = $ins->get_current_estatus ();
		if ($estatus->get_estatus()->clave == 'LI') {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' ya tiene una licencia activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::licenciaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else if (!$estatus->isActivo ()) {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' no está activo, por lo tanto no se puede aplicar una licencia. Estatus: '.((string) $estatus));
			
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
			
			/*$gsettings = new Gatuf_GSetting ();
			$gsettings->setApp ('Patricia');
		
			$cal = $gsettings->getVal ('calendario_activo', null);
		
			$calendario_actual = new Pato_Calendario ($cal);*/
			
			/* Cerrar el estatus anterior */
			$estatus->fin = date ('Y-m-d H:i:s');
			$estatus->update ();
			
			$estatus = new Pato_InscripcionEstatus ();
			$estatus->inicio = date ('Y-m-d H:i:s');
			$estatus->inscripcion = $ins;
			$estatus->estatus = new Pato_Estatus ('LI');
			
			$estatus->create ();
			
			/* Poner en el log del sistema */
			Gatuf_Log::info (sprintf ('El alumno %s cambió su estatus a Licencia. Movimiento por %s', $alumno->codigo, $request->user->codigo));
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
		                                               'carrera' => $car,
		                                               'estatus' => $estatus),
		                                         $request);
	}
	
	public $bajaVoluntariaSeleccionar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
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
	
	public $bajaVoluntariaEjecutar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
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
		
		$estatus = $ins->get_current_estatus ();
		if ($estatus->get_estatus()->clave == 'BV') {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' ya está de baja voluntaria');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaVoluntariaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else if (!$estatus->isActivo ()) {
			$request->user->setMessage (2, 'Está pendiente ver si se puede aplicar una baja voluntaria cuando el alumno no está activo. Atte. Patricia');
			//$request->user->setMessage (2, 'El alumno '.((string) $alumno).' no está activo, por lo tanto no se puede aplicar una licencia. Estatus: '.((string) $estatus));
			
			//$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaVoluntariaSeleccionar');
			//return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Para poder marcar su calendario de egreso */
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
			$estatus->fin = date ('Y-m-d H:i:s');
			$estatus->update ();
			
			$estatus = new Pato_InscripcionEstatus ();
			$estatus->inicio = date ('Y-m-d H:i:s');
			$estatus->inscripcion = $ins;
			$estatus->estatus = new Pato_Estatus ('BV');
			$estatus->create ();
			
			$ins->egreso = $calendario_actual;
			$ins->update ();
			
			Gatuf_Log::info (sprintf ('El alumno %s cambió su estatus a Baja Voluntaria. Movimiento por %s', $alumno->codigo, $request->user->codigo));
			$request->user->setMessage (1, 'El alumno '.((string) $alumno).' ha sido dado de baja. Causa: Baja Voluntaria (BV).');
			
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
		                                               'carrera' => $car,
		                                               'estatus' => $estatus),
		                                         $request);
	}
	
	public $cambioCarrera_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
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
	
	public $cambioCarreraAlumno_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function cambioCarreraAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins === null) {
			/* No está activo, no podemos moverlo de carrera */
			$request->user->setMessage (3, 'El alumno seleccionado no tiene una carrera activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::cambioCarrera');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$estatus = $ins->get_current_estatus ();
		if (!$estatus->isActivo ()) {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' no está activo, por lo tanto no se puede cambiar de carrera. Estatus: '.((string) $estatus));
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::cambioCarrera');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Estatus_CambioCarrera ($request->POST);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$egreso = new Pato_Calendario ($data['egreso']);
				/* Registrar los cambios de estatus */
				
				/* Primero, cerrar la vieja carrera */
				$estatus->fin = date ('Y-m-d H:i:s');
				$estatus->update ();
				
				$estatus = new Pato_InscripcionEstatus ();
				$estatus->inicio = date ('Y-m-d H:i:s');
				$estatus->estatus = new Pato_Estatus ('CC');
				$estatus->inscripcion = $ins;
				$estatus->create ();
				
				$ins->egreso = $egreso;
				$ins->update ();
				
				/* Crear la nueva inscripcion */
				$nueva = new Pato_Carrera ($data['carrera']);
				
				$gconf = new Gatuf_GSetting ();
				$gconf->setApp ('Patricia');
				$ingreso = new Pato_Calendario ($gconf->getVal ('calendario_activo', null));
				
				$inscripcion = new Pato_Inscripcion ();
				$inscripcion->alumno = $alumno;
				$inscripcion->ingreso = $ingreso;
				$inscripcion->carrera = $nueva;
				$inscripcion->turno = $data['turno'];
				
				$inscripcion->create ();
				
				$estatus = new Pato_InscripcionEstatus ();
				$estatus->inicio = date ('Y-m-d H:i:s');
				$estatus->estatus = new Pato_Estatus ('AC');
				$estatus->inscripcion = $inscripcion;
				$estatus->create ();
				
				/* Registrar en el log del sistema */
				Gatuf_Log::info (sprintf ('El alumno %s cambió de carrera. Movimiento por %s', $alumno->codigo, $request->user->codigo));
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
		                                               'inscripcion' => $ins,
		                                               'estatus' => $estatus),
		                                         $request);
	}
	
	public $bajaAcademica_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function bajaAcademica ($request, $match) {
		/* Nuevo reporte, buscar los alumnos que cumplen con una de 3 condiciones:
		 * La misma materia reprobada 3 veces o más en toda su carrera
		 * Más de 3 materias reprobadas en un semestre y también en otro semestre
		 * Más de 5 materias reprobadas en un semestre
		 */
		$kardex = new Pato_Kardex ();
		/* SELECT *, COUNT(*) FROM `kardex`  WHERE aprobada = 0 GROUP BY alumno,materia HAVING COUNT(*) > 1 */
		$kardex->_a['views']['doble'] = array ('group' => 'alumno,materia', 'having' => 'COUNT(*) > 2');
		
		$reprobados = $kardex->getList (array ('select' => 'alumno, materia, COUNT(*)', 'filter' => 'aprobada=0', 'view' => 'doble'));
		
		$reporte_a = array ();
		foreach ($reprobados as $r) {
			$alumno = $r->get_alumno ();
			
			$ins = $alumno->get_current_inscripcion ();
			
			if ($ins == null) continue; /* Ya está dado de baja */
			
			$estatus = $ins->get_current_estatus ();
			/* Aunque tenga Baja Administrativa, puede aplicar una baja academica */
			if ($estatus->isBaja() && $estatus->get_estatus()->clave != 'B6') continue; /* Ya está de baja */
			
			$sql = new Gatuf_SQL ('alumno = %s AND materia = %s AND aprobada = 0', array ($r->alumno, $r->materia));
			$muestra = $kardex->getList (array ('filter' => $sql->gen ()));
			
			$cals = array ();
			foreach ($muestra as $mu) {
				$cals[] = $mu->get_calendario ()->descripcion;
			}
			$cals = implode (', ', $cals);
			
			if (!isset ($reporte_a[$alumno->codigo])) {
				$o = new stdClass();
				$o->alumno = $alumno;
				$o->inscripcion = $ins;
				$o->estatus = $estatus;
				
				$o->reprobadas = array ();
				
				$p = new stdClass ();
				$p->materia = $r->get_materia ();
				$p->cals = $cals;
				
				$o->reprobadas[] = $p;
				$reporte_a[$alumno->codigo] = $o;
			} else {
				$p = new stdClass ();
				$p->materia = $r->get_materia ();
				$p->cals = $cals;
				
				$reporte_a[$alumno->codigo]->reprobadas[] = $p;
			}
		}
		
		$reporte_b = array ();
		$reporte_c = array ();
		
		/* SELECT *, COUNT(*) FROM `kardex`  WHERE aprobada = 0 GROUP BY alumno, calendario HAVING COUNT(*) > 1 */
		$kardex->_a['views']['multiples'] = array ('select' => 'alumno, calendario, COUNT(*) AS cant', 'group' => 'alumno, calendario', 'having' => 'COUNT(*) > 2', 'props' => array ('cant'));
		
		$reprobados = $kardex->getList (array ('filter' => 'aprobada=0', 'view' => 'multiples'));
		$strikes = array ();
		
		foreach ($reprobados as $r) {
			$alumno = $r->get_alumno ();
			
			$ins = $alumno->get_current_inscripcion ();
			
			if ($ins == null) {
				continue; /* Ya está dado de baja */
			}
			$estatus = $ins->get_current_estatus ();
			/* Aunque tenga Baja Administrativa, puede aplicar una baja academica */
			if ($estatus->isBaja() && $estatus->get_estatus()->clave != 'B6') {
				continue; /* Ya está de baja */
			}
			
			if ($r->cant >= 5) {
				/* Es del reporte C, más de 5 materias reprobadas en un mismo semestre */
				if (!isset ($reporte_c[$alumno->codigo])) {
					$o = new stdClass ();
					$o->alumno = $alumno;
					
					$o->inscripcion = $ins;
					$o->estatus = $estatus;
					
					$o->cals = array ();
					
					$p = new stdClass ();
					$p->cal = $r->get_calendario ();
					$p->cant = $r->cant;
				
					$o->cals[] = $p;
					$reporte_c[$alumno->codigo] = $o;
				} else {
					$p = new stdClass ();
					$p->cal = $r->get_calendario ();
					$p->cant = $r->cant;
					
					$reporte_c[$alumno->codigo]->cals[] = $p;
				}
			} else {
				if (!isset ($strikes[$alumno->codigo])) {
					/* Primer strike, marcar */
					$strikes[$alumno->codigo] = $r;
				} else if ($strikes[$alumno->codigo] !== 1) {
					/* Segundo strike */
					$o = new stdClass ();
					$o->alumno = $alumno;
					$o->inscripcion = $ins;
					$o->estatus = $estatus;
					
					$o->cals = array ();
					
					$first = $strikes[$alumno->codigo];
					
					$p = new stdClass ();
					$p->cal = $first->get_calendario ();
					$p->cant = $first->cant;
					
					$o->cals[] = $p;
					
					$p = new stdClass ();
					$p->cal = $r->get_calendario ();
					$p->cant = $r->cant;
					
					$o->cals[] = $p;
					
					$strikes[$alumno->codigo] = 1;
					
					$reporte_b[$alumno->codigo] = $o;
				} else {
					/* Anexar este strike */
					$p = new stdClass ();
					$p->cal = $r->get_calendario ();
					$p->cant = $r->cant;
					
					$reporte_b[$alumno->codigo]->cals[] = $p;
				}
			}
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-academica.html',
		                                         array('page_title' => 'Bajas Académicas',
		                                               'reporte_a' => $reporte_a,
		                                               'reporte_b' => $reporte_b,
		                                               'reporte_c' => $reporte_c),
		                                         $request);
	}
	
	public $bajaAcademicaEjecutar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function bajaAcademicaEjecutar ($request, $match) {
		$kardex = new Pato_Kardex ();
		/* SELECT *, COUNT(*) FROM `kardex`  WHERE aprobada = 0 GROUP BY alumno,materia HAVING COUNT(*) > 1 */
		$kardex->_a['views']['doble'] = array ('group' => 'alumno,materia', 'having' => 'COUNT(*) > 2');
		$reprobados = $kardex->getList (array ('select' => 'alumno, materia, COUNT(*)', 'filter' => 'aprobada=0', 'view' => 'doble'));
		
		$todos = array ();
		foreach ($reprobados as $r) {
			$alumno = $r->get_alumno ();
			$ins = $alumno->get_current_inscripcion ();
			
			if ($ins == null) continue; /* Ya está dado de baja */
			
			$estatus = $ins->get_current_estatus ();
			/* Aunque tenga Baja Administrativa, puede aplicar una baja academica */
			if ($estatus->isBaja() && $estatus->get_estatus()->clave != 'B6') continue; /* Ya está de baja */
			if (!isset ($todos[$alumno->codigo])) {
				$todos[$alumno->codigo] = $alumno;
			}
		}
		
		/* SELECT *, COUNT(*) FROM `kardex`  WHERE aprobada = 0 GROUP BY alumno, calendario HAVING COUNT(*) > 1 */
		$kardex->_a['views']['multiples'] = array ('select' => 'alumno, calendario, COUNT(*) AS cant', 'group' => 'alumno, calendario', 'having' => 'COUNT(*) > 2', 'props' => array ('cant'));
		
		$reprobados = $kardex->getList (array ('filter' => 'aprobada=0', 'view' => 'multiples'));
		$strikes = array ();
		
		foreach ($reprobados as $r) {
			$alumno = $r->get_alumno ();
			
			$ins = $alumno->get_current_inscripcion ();
			
			if ($ins == null) continue; /* Ya está dado de baja */
			$estatus = $ins->get_current_estatus ();
			/* Aunque tenga Baja Administrativa, puede aplicar una baja academica */
			if ($estatus->isBaja() && $estatus->get_estatus()->clave != 'B6') continue; /* Ya está de baja */
			
			if ($r->cant >= 5) {
				/* Es del reporte C, más de 5 materias reprobadas en un mismo semestre */
				if (!isset ($todos[$alumno->codigo])) {
					$todos[$alumno->codigo] = $alumno;
				}
			} else {
				if (!isset ($strikes[$alumno->codigo])) {
					/* Primer strike, marcar */
					$strikes[$alumno->codigo] = 1;
				} else {
					/* Segundo o siguientes strikes */
					if (!isset ($todos[$alumno->codigo])) {
						$todos[$alumno->codigo] = $alumno;
					}
				}
			}
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$calendario = $form->save ();
				
				$b5 = new Pato_Estatus ('B5'); /* Baja academica */
			
				foreach ($todos as $alumno) {
					/* TODO: Generar un log interno aquí */
					$ins = $alumno->get_current_inscripcion ();
					
					$old_estatus = $ins->get_current_estatus ();
					$old_estatus->fin = date ('Y-m-d H:i:s');
					$old_estatus->update ();
					
					$estatus = new Pato_InscripcionEstatus ();
					$estatus->inicio = date ('Y-m-d H:i:s');
					$estatus->inscripcion = $ins;
					$estatus->estatus = $b5;
					$estatus->create ();
			
					$ins->egreso = $calendario;
					$ins->update ();
			
					/* Registrar los cambios de estatus */
					/* Desmatricular las posibles materias que tenga */
					foreach ($alumno->get_grupos_list as $seccion) {
						/* Borrar las calificaciones y asistencias
						 * TODO: Convertir esto en un TRIGGER */
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
				}
				
				$request->user->setMessage (1, 'Todas las bajas académicas fueron aplicadas');
			
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAcademica');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Seleccionar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-academica-ejecutar.html',
		                                         array('page_title' => 'Baja Académica',
		                                               'total' => count ($todos),
		                                               'form' => $form),
		                                         $request);
	}
	
	public $bajaAdministrativaSeleccionar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function bajaAdministrativaSeleccionar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAdministrativaAlumno', $alumno->codigo);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-administrativa-seleccionar.html',
		                                         array('page_title' => 'Baja Administrativa',
		                                               'form' => $form),
		                                         $request);
	}
	
	public $bajaAdministrativaAlumno_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function bajaAdministrativaAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins === null) {
			/* No está activo, no podemos moverlo de carrera */
			$request->user->setMessage (3, 'El alumno seleccionado no tiene una carrera activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAdministrativaSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$estatus = $ins->get_current_estatus ();
		// No necesita estar activo para la Baja Administrativa
		/*if (!$estatus->isActivo ()) {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' no está activo, por lo tanto no se puede cambiar de carrera. Estatus: '.((string) $estatus));
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAdministrativa');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}*/
		/* Para poder marcar su calendario de egreso */
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		
		$cal = $gsettings->getVal ('calendario_activo', null);
		
		$calendario_actual = new Pato_Calendario ($cal);
		
		if ($request->method == 'POST') {
/* Registrar los cambios de estatus */
			$estatus->fin = date ('Y-m-d H:i:s');
			$estatus->update ();
			
			$estatus = new Pato_InscripcionEstatus ();
			$estatus->inicio = date ('Y-m-d H:i:s');
			$estatus->inscripcion = $ins;
			$estatus->estatus = new Pato_Estatus ('B6');
			$estatus->create ();
			
			//$ins->egreso = $calendario_actual;
			//$ins->update ();
			
			Gatuf_Log::info (sprintf ('El alumno %s cambió su estatus a Baja Administrativa (B6). Movimiento por %s', $alumno->codigo, $request->user->codigo));
			$request->user->setMessage (1, 'El alumno '.((string) $alumno).' ha sido dado de baja. Causa: Baja Administrativa (B6).');
			
			/* Redirigir al estatus del alumno */
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::kardex', $alumno->codigo);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$car = $ins->get_carrera ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-administrativa.html',
		                                         array('page_title' => 'Baja Administrativa',
		                                               'alumno' => $alumno,
		                                               'inscripcion' => $ins,
		                                               'carrera' => $car,
		                                               'estatus' => $estatus),
		                                         $request);
	}
	
	public $bajaAdministrativaRegresarSeleccionar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function bajaAdministrativaRegresarSeleccionar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAdministrativaRegresar', $alumno->codigo);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-administrativa-regresar-seleccionar.html',
		                                         array('page_title' => 'Regresar de baja administrativa',
		                                               'form' => $form),
		                                         $request);
	}
	
	public $bajaAdministrativaRegresar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function bajaAdministrativaRegresar ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins === null) {
			/* No está activo, no podemos moverlo de carrera */
			$request->user->setMessage (3, 'El alumno seleccionado no tiene una carrera activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAdministrativaRegresarSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$estatus = $ins->get_current_estatus ();
		// Necesita tener una baja administrativa
		if ($estatus->get_estatus()->clave != 'B6') {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' no tiene baja administrativa, por lo tanto no se puede revertir a activo. Estatus actual: '.((string) $estatus));
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::bajaAdministrativaRegresarSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			/* Registrar los cambios de estatus */
			$estatus->fin = date ('Y-m-d H:i:s');
			$estatus->update ();
			
			$estatus = new Pato_InscripcionEstatus ();
			$estatus->inicio = date ('Y-m-d H:i:s');
			$estatus->inscripcion = $ins;
			$estatus->estatus = new Pato_Estatus ('AC');
			$estatus->create ();
			
			//$ins->egreso = $calendario_actual;
			//$ins->update ();
			
			Gatuf_Log::info (sprintf ('El alumno %s cambió su estatus a Activo (AC). Movimiento por %s', $alumno->codigo, $request->user->codigo));
			$request->user->setMessage (1, 'El alumno '.((string) $alumno).' ha regresado de la muerte administrativa. Estatus: Activo (AC)');
			
			/* Redirigir al estatus del alumno */
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::kardex', $alumno->codigo);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$car = $ins->get_carrera ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/baja-administrativa-regresar.html',
		                                         array('page_title' => 'Regresar de baja administrativa',
		                                               'alumno' => $alumno,
		                                               'inscripcion' => $ins,
		                                               'carrera' => $car,
		                                               'estatus' => $estatus),
		                                         $request);
	}
	
	public $recibirDocumentosSeleccionar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function recibirDocumentosSeleccionar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::recibirDocumentos', $alumno->codigo);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarAlumno (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/recibir-documentos-seleccionar.html',
		                                         array('page_title' => 'Recibir documentos',
		                                               'form' => $form),
		                                         $request);
	}
	
	public $recibirDocumentos_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function recibirDocumentos ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		
		if ($ins === null) {
			/* No está activo, no podemos moverlo de carrera */
			$request->user->setMessage (3, 'El alumno seleccionado no tiene una carrera activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::recibirDocumentosSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$estatus = $ins->get_current_estatus ();
		// Necesita tener una baja administrativa
		if ($estatus->get_estatus()->clave != 'B2') {
			$request->user->setMessage (2, 'El alumno '.((string) $alumno).' no tiene estatus pendiente de de documentos. Estatus actual: '.((string) $estatus));
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::recibirDocumentosSeleccionar');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Estatus_Documentos ($request->POST);
			
			if ($form->isValid ()) {
				$docs_id = $form->save ();
				
				$doc = new Admision_Documento ();
				
				foreach ($docs_id as $id) {
					$doc->get ($id);
					
					$alumno->setAssoc ($doc);
				}
				
				/* Registrar los cambios de estatus */
				$estatus->fin = date ('Y-m-d H:i:s');
				$estatus->update ();
				
				$estatus = new Pato_InscripcionEstatus ();
				$estatus->inicio = date ('Y-m-d H:i:s');
				$estatus->inscripcion = $ins;
				$estatus->estatus = new Pato_Estatus ('AC');
				$estatus->create ();
				
				Gatuf_Log::info (sprintf ('El alumno %s cambió su estatus a Activo (AC). Movimiento por %s', $alumno->codigo, $request->user->codigo));
				$request->user->setMessage (1, 'Se ha recibido documentos del alumno '.((string) $alumno).' . Estatus: Activo (AC)');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Estatus::recibirDocumentosReporte', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Estatus_Documentos (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/recibir-documentos.html',
		                                         array('page_title' => 'Recibir documentos',
		                                               'alumno' => $alumno,
		                                               'form' => $form),
		                                         $request);
	}
	
	public $recibirDocumentosReporte_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function recibirDocumentosReporte ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$docs = $alumno->get_documentos_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/estatus/recibir-documentos-reporte.html',
		                                         array('page_title' => 'Documentos del alumno',
		                                               'alumno' => $alumno,
		                                               'documentos' => $docs),
		                                         $request);
	}
	
	public $recibirDocumentosReportePDF_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.admin_estatus'));
	public function recibirDocumentosReportePDF ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if ($alumno->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Estatus_Documentos ('P', 'mm', 'Letter');
		
		$pdf->render ($alumno, $request->user);
		
		$pdf->Close ();
		
		$nombre = 'recibo_'.$alumno->codigo.'.pdf';
		$fln = $nombre.Gatuf_Utils::getPassword (6);
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$fln, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$fln, $nombre, 'application/pdf', true);
	}
}

<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Solicitud_Suficiencias {
	public function index ($request, $match) {
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/index.html',
		                                         array ('page_title' => 'Suficiencias',
		                                                'carreras' => $carreras),
		                                         $request);
	}
	
	public $solicitudes_precond = array ('Gatuf_Precondition::loginRequired');
	public function solicitudes ($request, $match) {
		if ($request->user->type != 'a') {
			$request->user->setMessage (3, 'Sólo los alumnos pueden solicitar suficiencias');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::index');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		$alumno = $request->user->extra;
		
		/* Recuperar su lista de solicitudes */
		$solicitudes = $alumno->get_pato_solicitud_suficiencia_list ();
		
		$abierto = $gconf->getVal ('suficiencias_abierta_'.$sig_calendario->clave, false);
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) $abierto = false;
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/lista-alumno.html',
		                                         array ('page_title' => 'Suficiencias',
		                                                'solicitudes' => $solicitudes,
		                                                'abierto' => $abierto,
		                                                'siguiente_calendario' => $sig_calendario),
		                                         $request);
	}
	
	public $nueva_precond = array ('Gatuf_Precondition::loginRequired');
	public function nueva ($request, $match) {
		if ($request->user->type != 'a') {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumno = $request->user->extra;
		
		$extra = array ('alumno' => $alumno);
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		$abierto = $gconf->getVal ('suficiencias_abierta_'.$sig_calendario->clave, false);
		
		if (!$abierto) {
			$request->user->setMessage (3, 'El periodo para solicitar suficiencias está cerrado');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			$request->user->setMessage (3, 'Alumno no activo');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			/* El formulario viene de regreso */
			$form = new Pato_Form_Solicitud_Suficiencia_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$suficiencia = $form->save ();
				
				$request->user->setMessage (1, 'Solicitud de suficiencia creada');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Solicitud_Suficiencia_Agregar (null, $extra);
		}
		
		$context = new Gatuf_Template_Context(array());
		$tmpl = new Gatuf_Template('pato/solicitud/suficiencia/terminos.html');
		$terms = Gatuf_Template::markSafe($tmpl->render($context));
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/agregar.html',
		                                         array ('page_title' => 'Nueva solicitud de suficiencia',
		                                                'alumno' => $alumno,
		                                                'siguiente_calendario' => $sig_calendario,
		                                                'form' => $form,
		                                                'terms' => $terms),
		                                         $request);
	}
	
	public $actualizar_precond = array ('Gatuf_Precondition::loginRequired');
	public function actualizar ($request, $match) {
		if ($request->user->type != 'a') {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumno = $request->user->extra;
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		$abierto = $gconf->getVal ('suficiencias_abierta_'.$sig_calendario->clave, false);
		
		$suficiencia = new Pato_Solicitud_Suficiencia ();
		
		if (false === ($suficiencia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($suficiencia->alumno != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$abierto) {
			$request->user->setMessage (3, 'El periodo para solicitar suficiencias está cerrado');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($suficiencia->estatus != 0) {
			$request->user->setMessage (3, 'Sólo puedes actualizar una suficiencia en estado Pendiente');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('suficiencia' => $suficiencia);
		
		if ($request->method == 'POST') {
			/* El formulario viene de regreso */
			$form = new Pato_Form_Solicitud_Suficiencia_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$suficiencia = $form->save ();
				
				$request->user->setMessage (1, 'Solicitud de suficiencia actualizada');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Solicitud_Suficiencia_Actualizar (null, $extra);
		}
		
		$context = new Gatuf_Template_Context(array());
		$tmpl = new Gatuf_Template('pato/solicitud/suficiencia/terminos.html');
		$terms = Gatuf_Template::markSafe($tmpl->render($context));
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/actualizar.html',
		                                         array ('page_title' => 'Actualizar solicitud de suficiencia',
		                                                'alumno' => $alumno,
		                                                'suficiencia' => $suficiencia,
		                                                'siguiente_calendario' => $sig_calendario,
		                                                'form' => $form,
		                                                'terms' => $terms),
		                                         $request);
	}
	
	public $eliminar_precond = array ('Gatuf_Precondition::loginRequired');
	public function eliminar ($request, $match) {
		if ($request->user->type != 'a') {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumno = $request->user->extra;
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		$abierto = $gconf->getVal ('suficiencias_abierta_'.$sig_calendario->clave, false);
		
		$suficiencia = new Pato_Solicitud_Suficiencia ();
		
		if (false === ($suficiencia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($suficiencia->alumno != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$abierto) {
			$request->user->setMessage (3, 'El periodo para solicitar suficiencias está cerrado');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($suficiencia->estatus != 0) {
			$request->user->setMessage (3, 'Sólo puedes eliminar una suficiencia en estado Pendiente');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$materia = $suficiencia->get_materia ();
		$suficiencia->delete ();
		
		$request->user->setMessage (1, 'La solicitud de suficiencia para la materia '.$materia->descripcion.' fué retirada.');
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::solicitudes');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $revisarCarrera_precond = array ('Pato_Precondition::coordinadorRequired');
	public function revisarCarrera ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if ($carrera->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		/* Empezar por listar todas las solicitudes */
		$todas = Gatuf::factory ('Pato_Solicitud_Suficiencia')->getList ();
		
		$sol_car = array ();
		/* Filtrar las que no son de esta carrera */
		foreach ($todas as $solicitud) {
			$alumno = $solicitud->get_alumno ();
			
			$ins = $alumno->get_current_inscripcion ();
			
			if ($ins->carrera == $carrera->clave) {
				$sol_car[] = $solicitud;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/revisar.html',
		                                         array ('page_title' => 'Lista de solicitudes de suficiencia',
		                                                'carrera' => $carrera,
		                                                'solicitudes' => $sol_car,
		                                                'siguiente_calendario' => $sig_calendario),
		                                         $request);
	}
	
	public $aprobarCarrera_precond = array ('Pato_Precondition::coordinadorRequired');
	public function aprobarCarrera ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if ($carrera->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		/* Empezar por listar todas las solicitudes */
		$todas = Gatuf::factory ('Pato_Solicitud_Suficiencia')->getList ();
		
		$solicitudes = array ();
		/* Filtrar las que no son de esta carrera */
		foreach ($todas as $solicitud) {
			$alumno = $solicitud->get_alumno ();
			
			$ins = $alumno->get_current_inscripcion ();
			
			if ($ins->carrera == $carrera->clave) {
				$solicitudes[] = $solicitud;
			}
		}
		
		$extra['solicitudes'] = $solicitudes;
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Solicitud_Suficiencia_Aprobar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Solicitud_Suficiencias::revisarCarrera', $carrera->clave);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Solicitud_Suficiencia_Aprobar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/aprobar.html',
		                                         array ('page_title' => 'Aprobar solicitudes de suficiencia',
		                                                'carrera' => $carrera,
		                                                'solicitudes' => $solicitudes,
		                                                'siguiente_calendario' => $sig_calendario,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $crearNRCs_precond = array ('Gatuf_Precondition::adminRequired');
	public function crearNRCs ($request, $match) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Cambiar al calendario siguiente */
		$sig_calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $sig_calendario->clave;
		
		$suficiencia = new Pato_Solicitud_Suficiencia ();
		$suficiencia->_a['views']['simple'] = array ('group' => 'materia,maestro', 'props' => array ('alumnos'));
		$aprobadas = $suficiencia->getList (array ('select' => 'materia, maestro, COUNT(*) AS alumnos', 'filter' => 'estatus=1', 'view' => 'simple'));
		
		if ($request->method == 'POST') {
			/* Confirmación de regreso */
			
			$creados = array ();
			
			/* Primero crear el nrc */
			foreach ($aprobadas as $sol) {
				/* Revisar si el NRC ya existe */
				$sql = new Gatuf_SQL ('materia=%s AND seccion LIKE %s AND maestro=%s', array ($sol->materia, 'S%', $sol->maestro));
				
				$seccion = new Pato_Seccion ();
				$posibles = $seccion->getList (array ('filter' => $sql->gen ()));
				if (count ($posibles) > 0) {
					$creados[$sol->materia.'_'.$sol->maestro] = $posibles[0];
					continue;
				}
				
				$max_s = $seccion->maxSeccion ($sol->materia, 'S'); /* Suficiencias */
				
				if ($max_s === null) {
					$n_seccion = 'S01';
				} else {
					$num = (int) substr ($max_s, 1);
					$num++;
					$n_seccion = 'S'.str_pad ($num, 2, '0', STR_PAD_LEFT);
				}
				
				$seccion->materia = $sol->get_materia ();
				$seccion->seccion = $n_seccion;
				$seccion->maestro = $sol->get_maestro ();
				$seccion->create ();
				
				$creados[$sol->materia.'_'.$sol->maestro] = $seccion;
			}
			
			/* Ahora matricular los alumnos */
			$sols = Gatuf::factory ('Pato_Solicitud_Suficiencia')->getList (array ('filter' => 'estatus = 1'));
			
			Gatuf::loadFunction ('Pato_Procedimiento_matricular');
			foreach ($sols as $sol) {
				$seccion = $creados[$sol->materia.'_'.$sol->maestro];
				$res = Pato_Procedimiento_matricular ($seccion, $sol->get_alumno(), false, false);
				
				if ($res !== true) {
					$request->user->setMessage (2, 'Falló al matricular el alumno ('.((string) $sol->get_alumno()).' a la suficiencia '.$seccion->nrc.' ('.$seccion->materia.') . Razon: '.$res);
				}
			}
			
			return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/reporte-creados.html',
		                                         array ('page_title' => 'Reporte de NRCs de suficiencias',
		                                                'creados' => $creados,
		                                                'siguiente_calendario' => $sig_calendario),
		                                         $request);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/solicitud/suficiencia/crear-nrcs.html',
		                                         array ('page_title' => 'Crear NRCs de suficiencias',
		                                                'solicitudes' => $aprobadas,
		                                                'siguiente_calendario' => $sig_calendario),
		                                         $request);
	}
}

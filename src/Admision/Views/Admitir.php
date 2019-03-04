<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Admision_Views_Admitir {
	public $index_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admitir_aspirantes'));
	public function index ($request, $match) {
		/* Elegir una convocatoria */
		$convocatorias = Gatuf::factory ('Admision_Convocatoria')->getList ();
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/index.html',
		                                         array('page_title' => 'Admitir alumnos',
		                                               'convocatorias' => $convocatorias),
                                                 $request);
	}
	
	public $admitir_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admitir_aspirantes'));
	public function admitir ($request, $match) {
		$convocatoria = new Admision_Convocatoria ();
		
		if (false === ($convocatoria->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$cupos = $convocatoria->get_admision_cupocarrera_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/carrera.html',
		                                         array('page_title' => 'Admitir alumnos para la convocatoria '.$convocatoria->descripcion,
		                                               'convocatoria' => $convocatoria,
		                                               'cupos' => $cupos),
                                                 $request);
	}
	
	public $admitirCarrera_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admitir_aspirantes'));
	public function admitirCarrera ($request, $match) {
		$cupo_carrera = new Admision_CupoCarrera ();
		
		if (false === ($cupo_carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($cupo_carrera->procesada == true) {
			$request->user->setMessage (3, 'Esta carrera ya ha sido procesada');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Admitir::admitir', $cupo_carrera->convocatoria);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$filter = 'ceneval IS NOT NULL';
		$alumnos = $cupo_carrera->get_alumnos_list(array ('filter' => $filter));
		
		$extra = array ('alumnos' => $alumnos, 'cupo_carrera' => $cupo_carrera);
		if ($request->method == 'POST') {
			$form = new Admision_Form_Admitir_Carrera ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				Gatuf_Log::info (sprintf ('El usuario %s admitió alumnos de la carrera %s en la convocatoria %s', $request->user->codigo, $cupo_carrera->carrera, $cupo_carrera->convocatoria));
				$request->user->setMessage (1, 'Sus selecciones han sido guardadas');
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Admitir::admitir', $cupo_carrera->convocatoria);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Admitir_Carrera (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/por_carrera.html',
		                                         array('page_title' => 'Admitir alumnos',
		                                               'convocatoria' => $cupo_carrera->get_convocatoria (),
		                                               'cupo_carrera' => $cupo_carrera,
		                                               'alumnos' => $alumnos,
		                                               'form' => $form),
                                                 $request);
	}
	
	public $verAspirante_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admision.admitir_aspirantes'));
	public function verAspirante ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/ver_popup.html',
		                                         array('page_title' => 'Aspirante '.$aspirante->id,
		                                               'aspirante' => $aspirante),
                                                 $request);
	}
	
	public $procesar_precond = array ('Gatuf_Precondition::adminRequired');
	public function procesar ($request, $match) {
		return new Gatuf_HTTP_Response ('Vista en revisión');
		$cupo_carrera = new Admision_CupoCarrera ();
		
		if (false === ($cupo_carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($cupo_carrera->procesada == true) {
			$request->user->setMessage (3, 'Esta carrera ya ha sido procesada');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Admitir::admitir', $cupo_carrera->convocatoria);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$filter = 'ceneval IS NOT NULL AND admision = 0';
		$alumnos = $cupo_carrera->get_alumnos_list(array ('filter' => $filter, 'count' => true));
		
		if ($alumnos > 0) {
			/* No podemos procesar esta lista porque aún hay pendientes */
			$request->user->setMessage (3, 'No se puede procesar esta carrera porque aún no se decide sobre todos los alumnos de la lista');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Admitir::admitir', $cupo_carrera->convocatoria);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$filter = 'ceneval IS NOT NULL AND admision = 1';
		$aspis = $cupo_carrera->get_alumnos_list(array ('filter' => $filter));
		
		if (count ($aspis) > $cupo_carrera->cupo) {
			/* No se puede procesar porque tiene más admitidos de los permitidos */
			$request->user->setMessage (3, 'No se puede procesar esta carrera tiene más alumnos admitidos de lo planificado');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Admitir::admitir', $cupo_carrera->convocatoria);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Ok, recorrer la lista, convertir cada aspirante en un alumno */
		$conv = $cupo_carrera->get_convocatoria ();
		$calendario = $conv->get_calendario ();
		$carrera = $cupo_carrera->get_carrera ();
		$estatus = new Pato_Estatus ('B2');
		
		$base = substr ($calendario->anio, -2, 2);
		
		switch ($calendario->letra) {
			case 'C':
				$base .= '1';
				break;
			case 'D':
				$base .= '2';
				break;
			case 'E':
				$base .= '3';
				break;
		}
		$n = 1;
		
		foreach ($aspis as &$aspi) {
			/* Intentar determinar el máximo número de matricula ya asignado */
			$n = $aspi->id - 20000;
			
			do {
				$nuevo_codigo = $base.str_pad($n, 5, '0', STR_PAD_LEFT);
				$sql = new Gatuf_SQL ('codigo LIKE %s', $nuevo_codigo);
				$alumnos = Gatuf::factory ('Pato_Alumno')->getList (array ('filter' => $sql->gen(), 'order' => 'codigo DESC', 'nb' => 1, 'count' => true));
			
				if ($alumnos != 0) {
					$n = $n + 1;
				}
			} while ($alumnos != 0);
			
			$aspi->matricula = $nuevo_codigo;
			
			$aspi->update ();
			
			/* Crear el alumno */
			$alumno = new Pato_Alumno ();
			$alumno->codigo = $aspi->matricula;
			$alumno->nombre = $aspi->nombre;
			$alumno->apellido = $aspi->apellido;
			$alumno->sexo = $aspi->sexo;
			
			$alumno->email = $aspi->email;
			
			$alumno->create ();
			
			/* Crear la inscripcion */
			$inscripcion = new Pato_Inscripcion ();
			$inscripcion->alumno = $alumno;
			$inscripcion->carrera = $carrera;
			$inscripcion->ingreso = $calendario;
			$inscripcion->turno = $aspi->turno_final;
			$inscripcion->egreso = null;
			
			$inscripcion->create ();
			
			/* Crear el estatus asociado a la inscripcion */
			$insestatus = new Pato_InscripcionEstatus ();
			$insestatus->inscripcion = $inscripcion;
			$insestatus->inicio = date ('Y-m-d H:i:s');
			$insestatus->estatus = $estatus;
			$insestatus->fin = null;
		
			$insestatus->create ();
			Gatuf::loadFunction ('Pato_Utils_referencia');
			$referencia = Pato_Utils_referencia ($alumno->codigo);
			
			/* Enviar el correo */
			$tmpl = new Gatuf_Template('admision/admitir/carta_aceptacion.txt');
			$context = new Gatuf_Template_Context (
			               array ('nombre' => $alumno->nombre,
			                      'apellido' => $alumno->apellido,
			                      'referencia' => $referencia));
			$email = new Gatuf_Mail (Gatuf::config ('from_email'), $aspi->email, 'Has sido aceptado en la UPZMG');
			$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
			$email->addTextMessage ($tmpl->render ($context));
			$email->sendMail ();
		}
		
		$cupo_carrera->procesada = true;
		
		$cupo_carrera->update ();
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/reporte_nuevos.html',
		                                         array('page_title' => 'Crear alumnos',
		                                               'convocatoria' => $conv,
		                                               'cupo_carrera' => $cupo_carrera,
		                                               'aspirantes' => $aspis),
                                                 $request);
	}
}

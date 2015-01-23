<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Evaluacion_Profesor {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		if ($request->user->type == 'a') {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals', $request->user->login);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/index.html',
		                                         array('page_title' => 'Evaluación a profesores'),
                                                 $request);
	}
	
	public $resultados_precond = array ('Pato_Precondition::loginRequired');
	public function resultados ($request, $match) {
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		$con_p = array ();
		
		$maestros = array ();
		foreach ($carreras as $c) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$c->clave) || $request->user->hasPerm ('Patricia.resultados_eval_profesores')) {
				$maestros[$c->clave] = array ();
				$con_p[] = $c;
			}
		}
		
		$respuestas = Gatuf::factory ('Pato_Evaluacion_Respuesta')->getList ();
		foreach ($respuestas as $res) {
			$alumno = $res->get_alumno ();
			$maestro = $res->get_seccion ()->get_maestro ();
			
			$ins = $alumno->get_inscripcion_for_cal ($request->calendario);
			
			if ($ins != null) {
				$carrera = $ins->get_carrera ();
				if (!$request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
					continue;
				}
				
				$maestros[$carrera->clave][$maestro->codigo] = $maestro;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/resultados.html',
		                                         array ('page_title' => 'Resultados de evaluación a profesores',
		                                                'carreras' => $con_p,
		                                                'maestros' => $maestros),
                                                 $request);
	}
	
	public $resultadoMaestro_precond = array ('Pato_Precondition::loginRequired');
	public function resultadoMaestro ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if (false === ($carrera->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave) && !$request->user->hasPerm ('Patricia.resultados_eval_profesores')) {
			throw new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$total = array ();
		$cant = array ();
		$comentarios = array ();
		$secciones = array ();
		
		$respuestas = Gatuf::factory ('Pato_Evaluacion_Respuesta')->getList ();
		foreach ($respuestas as $res) {
			$m_res = $res->get_seccion ()->get_maestro ();
			$alumno = $res->get_alumno ();
			if ($maestro->codigo != $m_res->codigo) continue;
			
			$ins = $alumno->get_inscripcion_for_cal ($request->calendario);
			
			if ($ins == null) continue;
			$c_res = $ins->get_carrera ();
			
			if ($c_res->clave != $carrera->clave) {
				continue;
			}
			
			if (!isset ($total[$res->seccion])) {
				$secciones[] = $res->get_seccion ();
				$total[$res->seccion] = array ();
				$cant[$res->seccion] = 0;
				$comentarios[$res->seccion] = array ();
				for ($g = 1; $g <= 27; $g++) {
					$total[$res->seccion][$g] = 0;
				}
			}
			
			for ($g = 1; $g <= 27; $g++) {
				$total[$res->seccion][$g] += $res->respuestas['p_'.$g];
			}
			
			if (trim ($res->respuestas['comentario']) != '') {
				$comentarios[$res->seccion][] = $res->respuestas['comentario'];
			}
			
			$cant[$res->seccion]++;
		}
		
		foreach ($total as $nrc => &$grupo) {
			for ($g = 1; $g <= 27; $g++) {
				$grupo[$g] /= $cant[$nrc];
			}
		}
		
		$textos = array (
			'Domina el profesor los contenidos de la materia que imparte',
			'Es evidente que el profesor prepara las clases o sesiones',
			'El profesor es ordenado y claro en la exposición de los temas',
			'Procura relacionar los nuevos conocimientos con lo visto anteriormente',
			'El profesor elabora síntesis, resúmenes o mapas conceptuales de lo revisado y de lo que va a explicar',
			'El profesor verifica al término de las sesiones si los alumnos han comprendido lo estudiado',
			'El profesor usa medios variados de apoyo al aprendizaje (blog, foro, otros)',
			'El profesor motiva a los alumnos para asistir a asesorias para resolver dudas',
			'Demuestra respeto el profesor a los juicios y opiniones de los alumno',
			'El profesor se expresa respetuosamente hacia los alumnos',
			'El profesor brinda una atención individual cuando se le solicita',
			'El profesor realizó al inicio de cada unidad de aprendizaje una evaluación diagnóstica para conocer el nivel de competencia de cada alumno',
			'Al inicio del curso, el profesor le indicó que tenía que desarrollar un portafolio de evidencias de la asignatura',
			'El profesor realizó una evaluación formativa durante el curso (evaluó los avances que tenía en prácticas, ejercicios, trabajos, tareas, etc.)',
			'Considera usted que el profesor utilizó todos los criterios de evaluación durante el curso (conocimientos, capacidades, habilidades, destrezas, actitudes, aptitudes y valores)',
			'Considera usted que en todas las unidades de aprendizaje, el profesor alcanzó los objetivos del proceso enseñanza-aprendizaje',
			'Motiva el profesor a los alumnos para preguntar y participar en clase',
			'Impulsa el trabajo en equipo',
			'Da a conocer los criterios de evaluación, contenido y objetivo del curso a los alumnos',
			'Es justo en las evaluaciones',
			'Usa diferentes mecanismos de evaluación, según los objetivos a evaluar (proyectos, prácticas, mapas conceptuales, rúbricas)',
			'Entrega con oportunidad los resultados de las evaluaciones realizadas',
			'Informa el profesor a los alumnos sobre los problemas detectados en las evaluaciones',
			'El profesor inicia y termina con puntualidad las sesiones programadas',
			'El profesor relaciona los contenidos de la materia con la práctica profesional de tu carrera',
			'¿Cómo evaluaría globalmente el desempeño de su profesor?',
			'La cordialidad y capacidad del profesor logra crear un clima de confianza para que el alumno pueda exponer sus problemas',
		);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/resultados-maestro.html',
		                                         array ('page_title' => 'Resultados de la evaluación',
		                                                'maestro' => $maestro,
		                                                'secciones' => $secciones,
		                                                'carrera' => $carrera,
		                                                'total' => $total,
		                                                'comentarios' => $comentarios,
		                                                'textos' => $textos),
                                                 $request);
	}
	
	public $listar_evals_precond = array ('Gatuf_Precondition::loginRequired');
	public function listar_evals ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (!$request->user->administrator && !$request->user->isCoord () && $request->user->login != $alumno->codigo) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		/* Si el calendario actual no es igual al calendario activo,
		 * No permitir las evaluaciones */
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		$cal = $gsettings->getVal ('calendario_activo', null);
		$calendario = new Pato_Calendario ($cal);
		
		$correcto = true;
		if ($calendario->clave != $request->calendario->clave) {
			$request->user->setMessage (1, 'No se permite evaluar calendarios anteriores');
			$correcto = false;
		}
		
		/* Revisar cuáles respuestas están en tiempo */
		$secciones = $alumno->get_grupos_list ();
		
		$respuestas = array ();
		
		foreach ($secciones as $seccion) {
			$sql = new Gatuf_SQL ('seccion=%s', $seccion->nrc);
			$rs = $alumno->get_pato_evaluacion_respuesta_list (array ('filter' => $sql->gen ()));
			
			/* Revisar si contestó la encuesta o no */
			if (count ($rs) == 0) {
				$respuestas[$seccion->nrc] = false;
			} else {
				$respuestas[$seccion->nrc] = true;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/listar.html',
		                                         array('page_title' => 'Evaluación a profesores',
		                                               'alumno' => $alumno,
		                                               'respuestas' => $respuestas,
		                                               'secciones' => $secciones,
		                                               'correcto' => $correcto),
                                                 $request);
	}
	
	public $evaluar_precond = array ('Gatuf_Precondition::loginRequired');
	public function evaluar ($request, $match) {
		if ($request->user->type != 'a') {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Si el calendario actual no es igual al calendario activo,
		 * No permitir las evaluaciones */
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		$cal = $gsettings->getVal ('calendario_activo', null);
		$calendario = new Pato_Calendario ($cal);
		
		if ($calendario->clave != $request->calendario->clave) {
			$request->user->setMessage (1, 'No se permite evaluar calendarios anteriores');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals', $request->user->login);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
		
		$alumno = $request->user->extra;
		$secs = $alumno->get_grupos_list (array ('filter' => $sql->gen ()));
		
		if (count ($secs) == 0) {
			/* El alumno no está matriculado en esta sección */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('seccion=%s', $seccion->nrc);
		$rs = $alumno->get_pato_evaluacion_respuesta_list (array ('filter' => $sql->gen ()));
		
		if (count ($rs) != 0) {
			/* El alumno ya respondió la encuesta */
			$request->user->setMessage (3, 'Esta evaluación ya fué contestada');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals', $request->user->login);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Evaluacion_Profesor ($request->POST);
			
			if ($form->isValid ()) {
				$respuestas = $form->save ();
				
				$resp = new Pato_Evaluacion_Respuesta ();
				
				$resp->revision = 1; /* Versión de la encuesta */
				$resp->alumno = $alumno;
				$resp->seccion = $seccion;
				$resp->respuestas = $respuestas;
				
				$resp->create ();
				
				$request->user->setMessage (1, 'Evaluación completa');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals', $request->user->login);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Evaluacion_Profesor (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/evaluar.html',
		                                         array('page_title' => 'Evaluar profesor',
		                                               'alumno' => $alumno,
		                                               'form' => $form,
		                                               'seccion' => $seccion),
                                                 $request);
	}
}

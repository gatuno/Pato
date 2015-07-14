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
	
	public $resultados_precond = array ('Gatuf_Precondition::loginRequired');
	public function resultados ($request, $match) {
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		$con_p = array ();
		
		$maestros = array ();
		
		/* Query original
		SELECT DISTINCT maestro,carrera FROM eval_alum_prof AS EAP
		LEFT JOIN secciones AS S ON S.nrc = EAP.seccion
		LEFT JOIN patricia.inscripciones AS I ON EAP.alumno = I.alumno AND I.egreso IS NULL */
		$respuesta_model = new Pato_Evaluacion_Respuesta ();
		
		$s_model = new Pato_Seccion ();
		
		$s_model_t = $s_model->getSqlTable ();
		$r_model_t = $respuesta_model->getSqlTable ();
		
		$a_actuales = $s_model->_con->dbname.'.'.$s_model->_con->pfx.'alumnos_actuales';
		
		$respuesta_model->_a['views']['por_m']['select'] = 'DISTINCT S.maestro, A.carrera';
		$respuesta_model->_a['views']['por_m']['join'] = sprintf ('LEFT JOIN %s AS S ON S.nrc = %s.seccion INNER JOIN %s AS A ON %s.alumno = A.alumno', $s_model_t, $r_model_t, $a_actuales, $r_model_t);
		$respuesta_model->_a['views']['por_m']['props'] = array ('maestro', 'carrera');
		
		$maestro = new Pato_Maestro ();
		foreach ($carreras as $c) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$c->clave) || $request->user->hasPerm ('Patricia.resultados_eval_profesores')) {
				$con_p[] = $c;
				$maestros[$c->clave] = array ();
				$respuesta_model->_a['views']['por_m']['where'] = sprintf ('A.carrera = %s', $s_model->_con->esc($c->clave));
				$lista = $respuesta_model->getList (array ('view' => 'por_m'));
				
				foreach ($lista as $l) {
					$maestro->get ($l->maestro);
					$maestros[$c->clave][] = clone ($maestro);
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/resultados.html',
		                                         array ('page_title' => 'Resultados de evaluación a profesores',
		                                                'carreras' => $con_p,
		                                                'maestros' => $maestros),
                                                 $request);
	}
	
	public $resultadoMaestro_precond = array ('Gatuf_Precondition::loginRequired');
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
		
		$respuesta_model = new Pato_Evaluacion_Respuesta ();
		
		$s_model = new Pato_Seccion ();
		
		$s_model_t = $s_model->getSqlTable ();
		$r_model_t = $respuesta_model->getSqlTable ();
		
		$a_actuales = $s_model->_con->dbname.'.'.$s_model->_con->pfx.'alumnos_actuales';
		
		$respuesta_model->_a['views']['por_m']['join'] = sprintf ('LEFT JOIN %s AS S ON S.nrc = %s.seccion INNER JOIN %s AS A ON %s.alumno = A.alumno', $s_model_t, $r_model_t, $a_actuales, $r_model_t);
		$respuesta_model->_a['views']['por_m']['where'] = sprintf ('A.carrera = %s AND S.maestro = %s', $s_model->_con->esc($carrera->clave), $maestro->_con->esc ($maestro->codigo));
		
		$respuestas = $respuesta_model->getList (array ('view' => 'por_m'));
		
		foreach ($respuestas as $res) {
			$alumno = $res->get_alumno ();
			
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
		
		/* Si el calendario actual no es igual al calendario marcado en la configuración,
		 * No permitir las evaluaciones */
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		$correcto = true;
		
		$abierto = $gsettings->getVal ('evaluacion_profesores_abierta', false);
		
		if ($abierto == false) {
			$correcto = false;
			$request->user->setMessage (2, 'Por el momento, la evaluación de profesores no se encuentra activa');
		}
		
		$cal = $gsettings->getVal ('evaluacion_profesores_cal', '');
		$calendario = new Pato_Calendario ($cal);
		
		if ($correcto && $calendario->clave != $request->calendario->clave) {
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
	
	public static function checar_si_evaluo ($alumno) {
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		
		/* Si no está abierta la evaluación de profesores, no sirve de nada forzar a que evaluen */
		$abierto = $gsettings->getVal ('evaluacion_profesores_abierta', false);
		
		if (!$abierto) {
			return true;
		}
		
		$cal = $gsettings->getVal ('evaluacion_profesores_cal', '');
		$calendario = new Pato_Calendario ($cal);
		
		/* Mover al calendario activo actual */
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		//$request->session->setData ('CAL_ACTIVO', $calendario->clave);
		
		/* Revisar cuáles respuestas están en tiempo */
		$secciones = $alumno->get_grupos_list ();
		
		$respuestas = 0;
		
		foreach ($secciones as $seccion) {
			$sql = new Gatuf_SQL ('seccion=%s', $seccion->nrc);
			$rs = $alumno->get_pato_evaluacion_respuesta_list (array ('filter' => $sql->gen ()));
			
			/* Revisar si contestó la encuesta o no */
			if (count ($rs) != 0) {
				$respuestas++;
			}
		}
		
		/* Verdadero si evaluó todas los grupos */
		return (count ($secciones) == $respuestas);
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
		
		/* Si el calendario actual no es igual al calendario de las preferencias,
		 * No permitir las evaluaciones */
		$gsettings = new Gatuf_GSetting ();
		$gsettings->setApp ('Patricia');
		$cal = $gsettings->getVal ('evaluacion_profesores_cal', '');
		$calendario = new Pato_Calendario ($cal);
		
		if ($calendario->clave != $request->calendario->clave) {
			$request->user->setMessage (1, 'No se permite evaluar calendarios anteriores');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals', $request->user->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$abierto = $gsettings->getVal ('evaluacion_profesores_abierta', false);
		
		if ($abierto == false) {
			$request->user->setMessage (2, 'Por el momento, la evaluación de profesores no se encuentra activa');
			
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

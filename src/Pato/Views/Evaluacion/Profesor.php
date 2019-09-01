<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Evaluacion_Profesor {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		if (get_class ($request->user) == 'Pato_Alumno') {
			return self::listar_evals ($request, $match);
		} else {
			if (!$request->user->hasPerm ('Patricia.resultados_eval_profesores')) {
				return new Gatuf_HTTP_Response_Forbidden($request);
			}
			
			return self::resultados ($request, $match);
		}
	}
	
	//public $resultados_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.resultados_eval_profesores'));
	private static function resultados ($request, $match) {
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
			$con_p[] = $c;
			$maestros[$c->clave] = array ();
			$respuesta_model->_a['views']['por_m']['where'] = sprintf ('A.carrera = %s', $s_model->_con->esc($c->clave));
			$lista = $respuesta_model->getList (array ('view' => 'por_m'));
			
			foreach ($lista as $l) {
				$maestro->get ($l->maestro);
				$maestros[$c->clave][] = clone ($maestro);
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/resultados.html',
		                                         array ('page_title' => 'Resultados de evaluación a profesores',
		                                                'carreras' => $con_p,
		                                                'maestros' => $maestros),
                                                 $request);
	}
	
	public $resultadoMaestro_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.resultados_eval_profesores'));
	public function resultadoMaestro ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if (false === ($carrera->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
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
				for ($g = 1; $g <= 26; $g++) {
					$total[$res->seccion][$g] = 0;
				}
			}
			
			for ($g = 1; $g <= 26; $g++) {
				$total[$res->seccion][$g] += $res->respuestas['p_'.$g];
			}
			
			if (trim ($res->respuestas['comentario']) != '') {
				$comentarios[$res->seccion][] = $res->respuestas['comentario'];
			}
			
			$cant[$res->seccion]++;
		}
		
		foreach ($total as $nrc => &$grupo) {
			for ($g = 1; $g <= 26; $g++) {
				$grupo[$g] /= $cant[$nrc];
			}
		}
		
		$textos = array (
			'Asiste normalmente a clase.',
			'Cumple adecuadamente con el inicio y término del horario de clase.',
			'Al inicio del curso da a conocer el programa (competencias, contenidos, metodología, criterio de evaluación).',
			'El profesor cumple con los resultados de aprendizaje, contenido, metodología y criterio de evaluación que se dio a conocer al principio del curso.',
			'He desarrollado las competencias planteadas en el programa al concluir el cuatrimestre.',
			'Se han visto en clase los temas contenidos en el programa de la materia o curso.',
			'Aclara mis dudas.',
			'Cuando solicita actividades de aprendizaje los devuelve con comentarios u observaciones.',
			'Utiliza con frecuencia ejemplos, esquemas, presentaciones, modelos o gráficos, para apoyar sus explicaciones.',
			'Expone claramente los temas de la materia.',
			'Las actividades que se realizaron en las sesiones de clase, dan evidencia que el profesor se ocupa de la planeación.',
			'Hace reflexionar en las implicaciones o aplicaciones prácticas de lo tratado en clase.',
			'Promueve el uso de diversas herramientas, particularmente las digitales, para gestionar (recabar, procesar, evaluar y usar) información.',
			'Promueve actividades participativas que me permiten colaborar con mis compañeros con una actitud positiva.',
			'La comunicación profesor(a)-estudiante crea un clima de confianza.',
			'Promueve valores como el respeto, tolerancia, equidad, responsabilidad, honestidad, entre otros.',
			'La clase se desarrolla en un ambiente de respeto, tolerancia y equidad.',
			'Los evidencias que solicita el profesor evalúan el contenido temático del programa de la materia.',
			'Los criterios y procedimientos de evaluación se aplicaron tal como fueron presentados al inicio del curso.',
			'Se informa detalladamente la calificación obtenida en el curso.',
			'Es posible revisar con el profesor la calificación, si se considera que puede haber error.',
			'Además de los exámenes, las evaluaciones del profesor se basan en los temas vistos en clase.',
			'Da a conocer las calificaciones en el plazo establecido (actividades de aprendizaje, evidencias, etc.).',
			'La calificación final toma en cuenta el trabajo de todo el curso.',
			'Estoy satisfecho(a) con la labor docente de este(a) profesor(a).',
			'Considero que el profesor(a) me ha brindado los elementos necesarios para que desarrolle las habilidades planteadas en la materia.',
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
	
	//public $listar_evals_precond = array ('Pato_Precondition::alumnoRequired');
	private static function listar_evals ($request, $match) {
		//return new Gatuf_HTTP_Response ('Vista en remodelación');
		$alumno = $request->user;
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		$correcto = true;
		
		$abierto = $gconf->getVal ('evaluacion_profesores_abierta', false);
		$cal = $gconf->getVal ('evaluacion_profesores_cal', '');
		
		$calendario = new Pato_Calendario ();
		
		if (false === ($calendario->get ($cal))) {
			$abierto = false;
			$calendario = null;
		}
		
		if ($abierto == false) {
			$correcto = false;
			$request->user->setMessage (2, 'Por el momento, la evaluación de profesores no se encuentra activa');
		}
		
		/* Revisar cuáles respuestas están en tiempo */
		
		if ($calendario != null) {
			$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
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
		} else {
			$secciones = array ();
			$respuestas = array ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/listar.html',
		                                         array('page_title' => 'Evaluación a profesores',
		                                               'alumno' => $alumno,
		                                               'respuestas' => $respuestas,
		                                               'secciones' => $secciones,
		                                               'correcto' => $correcto,
		                                               'calendario' => $calendario),
                                                 $request);
	}
	
	public static function checar_si_evaluo ($alumno) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		/* Si no está abierta la evaluación de profesores, no sirve de nada forzar a que evaluen */
		$abierto = $gconf->getVal ('evaluacion_profesores_abierta', false);
		$cal = $gconf->getVal ('evaluacion_profesores_cal', '');
		
		$calendario = new Pato_Calendario ();
		
		if (false === ($calendario->get ($cal))) {
			$abierto = false;
			return true;
		}
		
		if (!$abierto) {
			return true;
		}
		
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
	
	public $evaluar_precond = array ('Pato_Precondition::alumnoRequired');
	public function evaluar ($request, $match) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$abierto = $gconf->getVal ('evaluacion_profesores_abierta', false);
		$cal = $gconf->getVal ('evaluacion_profesores_cal', '');
		
		$calendario = new Pato_Calendario ();
		
		if (false === ($calendario->get ($cal))) {
			$abierto = false;
		}
		
		if ($abierto == false) {
			$request->user->setMessage (2, 'Por el momento, la evaluación de profesores no se encuentra activa');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::index');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
		
		$alumno = $request->user;
		$secs = $alumno->get_grupos_list (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($secs == 0) {
			/* El alumno no está matriculado en esta sección */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('seccion=%s', $seccion->nrc);
		$rs = $alumno->get_pato_evaluacion_respuesta_list (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($rs != 0) {
			/* El alumno ya respondió la encuesta */
			$request->user->setMessage (3, 'Esta evaluación ya fué contestada');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::index');
			
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
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::index');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Evaluacion_Profesor (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/evaluaciones/evaluar.html',
		                                         array('page_title' => 'Evaluar profesor',
		                                               'alumno' => $alumno,
		                                               'form' => $form,
		                                               'seccion' => $seccion,
		                                               'calendario' => $calendario),
                                                 $request);
	}
}

<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Evaluacion_Profesor {
	public $listar_evals = array ('Gatuf_Precondition::loginRequired');
	public function listar_evals ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
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
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
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
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals');
			
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
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Evaluacion_Profesor::listar_evals');
			
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

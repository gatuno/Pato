<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Asignatura_Planeacion {
	public $ver_precond = array ('Gatuf_Precondition::loginRequired');
	public function ver ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/asignatura/cerrado.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion),
		                                         $request);
		/*$es_el_dueno = ($request->user->type == 'm' && ($seccion->maestro == $request->user->login || $seccion->suplente == $request->user->login));
		
		if (!$es_el_dueno && !$request->user->isCoord ()) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$planes = $seccion->get_pato_asignatura_planeacion_list (array ('order' => array ('programada ASC')));
		setlocale (LC_TIME, 'es_MX');
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		$abierto = $gconf->getVal ('planeacion_asignatura_'.$request->calendario->clave, false);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/asignatura/planeacion/ver.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'es_el_dueno' => $es_el_dueno,
		                                                'abierto' => $abierto,
		                                                'planes' => $planes),
		                                         $request);*/
	}
	
	public $agregarPlan_precond = array ('Gatuf_Precondition::loginRequired');
	public function agregarPlan ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/asignatura/cerrado.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion),
		                                         $request);
		
		/*$es_el_dueno = ($request->user->type == 'm' && ($seccion->maestro == $request->user->login || $seccion->suplente == $request->user->login));
		
		if (!$es_el_dueno) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		$abierto = $gconf->getVal ('planeacion_asignatura_'.$request->calendario->clave, false);
		if (!$abierto) {
			$request->user->setMessage (3, 'No puede planear las unidades de su asignatura porque está cerrado');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Asignatura_Planeacion::ver', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('seccion' => $seccion);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Asignatura_Planeacion_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$plan = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Asignatura_Planeacion::ver', $seccion->nrc);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Asignatura_Planeacion_Agregar (null, $extra);
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/asignatura/planeacion/agregar-plan.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);*/
	}
	
	public $seguimiento_precond = array ('Gatuf_Precondition::loginRequired');
	public function seguimiento ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/asignatura/cerrado.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion),
		                                         $request);
		/*$plan = new Pato_Asignatura_Planeacion ();
		
		if (false === ($plan->get($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($plan->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$es_el_dueno = ($request->user->type == 'm' && ($seccion->maestro == $request->user->login || $seccion->suplente == $request->user->login));
		
		if (!$es_el_dueno) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($plan->getSeguimiento () !== null) {
			$request->user->setMessage (3, 'Esta actividad ya tiene registrado un seguimiento');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Asignatura_Planeacion::ver', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('plan' => $plan);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Asignatura_Seguimiento_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$seguimiento = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Asignatura_Planeacion::ver', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Asignatura_Seguimiento_Agregar (null, $extra);
		}
		
		setlocale (LC_TIME, 'es_MX');
		$titulo = sprintf ("Sección %s - %s %s", $seccion->nrc, $seccion->get_materia()->descripcion, $seccion->seccion);
		return Gatuf_Shortcuts_RenderToResponse ('pato/asignatura/seguimiento/agregar.html',
		                                         array ('page_title' => $titulo,
		                                                'seccion' => $seccion,
		                                                'plan' => $plan,
		                                                'form' => $form),
		                                         $request);*/
	}
}

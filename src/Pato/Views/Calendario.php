<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Calendario {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		$calendarios = Gatuf::factory ('Pato_Calendario')->getList ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/index.html',
		                                         array ('page_title' => 'Calendarios',
		                                                'calendarios' => $calendarios),
		                                         $request);
	}
	
	public $agregarCalendario = array ('Gatuf_Precondition::adminRequired');
	public function agregarCalendario ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Agregar ($request->POST);
			
			if ($form->isValid ()) {
				$cal = $form->save ();
				
				$request->session->setData ('CAL_ACTIVO', $cal->clave);
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::index');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/agregar-calendario.html',
		                                         array ('page_title' => 'Nuevo calendario',
		                                                'form' => $form),
		                                         $request);
	}
	
	public function cambiarCalendario ($request, $match) {
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views::index');
		}
		
		if ($request->method == 'POST') {
			/* Si el formulario valida, cambiar el calendario */
			$form = new Pato_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$cal = $form->save ();
				
				$request->session->setData ('CAL_ACTIVO', $cal->clave);
			}
		}
		
		return new Gatuf_HTTP_Response_Redirect ($success_url);
	}
}

<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Horario {
	public $agregarHora_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = $seccion->get_materia ();
		
		$extra = array('seccion' => $seccion);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Horario_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Horario_Agregar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/horario/agregar-horario.html',
		                                         array ('page_title' => 'Agregar nueva hora',
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
		
	}
	
	public $eliminarHora_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$hora = new Pato_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST') {
			/* Adelante, eliminar esta hora */
			$hora->delete ();
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/horario/eliminar-horario.html',
		                                         array ('page_title' => 'Eliminar hora',
		                                                'seccion' => $seccion,
		                                                'salon' => $hora->get_salon (),
		                                                'horario' => $hora),
		                                         $request);
	}
	
	public $actualizarHora_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$hora = new Pato_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array('seccion' => $seccion, 'horario' => $hora);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Horario_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Horario_Actualizar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/horario/edit-horario.html',
		                                         array ('page_title' => 'Actualizar horario',
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
}
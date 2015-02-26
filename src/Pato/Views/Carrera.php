<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Carrera {
	
	public function index ($request, $match) {
		# Listar las carreras aquí
		$carreras = Gatuf::factory('Pato_Carrera')->getList();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/carrera/index.html',
		                                         array('page_title' => 'Carreras',
		                                               'carreras' => $carreras),
		                                         $request);
	}
	
	public function verCarrera ($request, $match) {
		/* Ver si esta carrera es válida */
		$carrera = new Pato_Carrera ();
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Verificar que la carrera esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Carrera::verCarrera', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$color = str_pad (dechex ($carrera->color), 6, '0', STR_PAD_LEFT);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/carrera/ver-carrera.html',
		                                         array ('carrera' => $carrera,
		                                                'color' => $color,
		                                                'page_title' => $carrera->descripcion),
		                                         $request);
	}
	
	public $agregarCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarCarrera ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Carrera_Agregar($request->POST);
			if ($form->isValid()) {
				$carrera = $form->save();
				
				Gatuf_Log::info (sprintf ('La carrera %s ha sido creada por el usuario %s (%s)', $carrera->clave, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Carrera::verCarrera', $carrera->clave);
				return new Gatuf_HTTP_Response_Redirect($url);
			}
		} else {
			$form = new Pato_Form_Carrera_Agregar(null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/carrera/agregar-carrera.html',
		                                         array('page_title' => 'Crear carrera',
		                                               'form' => $form),
		                                         $request);
	}
	
	public $actualizarCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarCarrera ($request, $match) {
		$carrera = new Pato_Carrera ();
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		/* Verificar que la carrera esté en mayúsculas */
		$nueva_clave = mb_strtoupper ($match[1]);
		if ($match[1] != $nueva_clave) {
			$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Carrera::actualizarCarrera', array ($nueva_clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('carrera' => $carrera);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Carrera_Actualizar($request->POST, $extra);
			
			if ($form->isValid()) {
				$carrera = $form->save ();
				
				Gatuf_Log::info (sprintf ('La carrera %s ha sido actualizada por el usuario %s (%s)', $carrera->clave, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView('Pato_Views_Carrera::verCarrera', $carrera->clave);
				return new Gatuf_HTTP_Response_Redirect($url);
			}
		} else {
			$form = new Pato_Form_Carrera_Actualizar(null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/carrera/edit-carrera.html',
		                                         array('page_title' => 'Actualizar carrera',
		                                               'carrera' => $carrera,
		                                               'form' => $form),
		                                         $request);
	}
}

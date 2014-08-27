<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Middleware_Calendario {
	function process_request (&$request) {
		/* Ignorar las vistas de login o recuperación de contraseñas */
		if (preg_match('#^/$#', $request->query) || preg_match('#^/login/$#', $request->query) || preg_match('#^/logout/$#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/password#', $request->query)) {
			return false;
		}
		
		/* Ignorar el índice de calendarios */
		if (preg_match('#^/calendarios#', $request->query)) {
			return false;
		}
		
		$request->calendario = new Pato_Calendario ();
		$cal = $request->session->getData ('CAL_ACTIVO', null);
		
		if ($cal == null) {
			$gsettings = new Gatuf_GSettings ();
			$gsettings->setApp ('Patricia');
			
			$cal = $gsettings->getVal ('calendario', null);
		}
		
		if (false === $request->calendario->get($cal)) {
			/* Si no existe el calendario seleccionado,
			 * Seleccionar el primero que no esté oculto */
			$ops = $request->calendario->getList (array ('filter' => 'oculto=0', 'order' => 'clave DESC'));
			
			if ($ops->count () == 0) {
				/* No hay calendarios disponibles, redirigir hacia la vista de calendarios */
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
			
			$request->calendario = $ops[0];
		}
		
		$GLOBALS['CAL_ACTIVO'] = $request->calendario->clave;
		$request->session->setData ('CAL_ACTIVO', $request->calendario->clave);
		
		Gatuf_Signal::connect('Gatuf_Template_Context_Request::construct', array('Pato_Middleware_Calendario', 'processContext'));
		return false;
	}
	
	public static function processContext($signal, &$params) {
        $params['context'] = array_merge($params['context'], array ('form_calendario' => new Pato_Form_Calendario_Seleccionar (null)));
	}
}


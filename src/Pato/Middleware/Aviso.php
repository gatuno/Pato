<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Middleware_Aviso {
	function process_request (&$request) {
		if ($request->user->isAnonymous ()) {
			return false;
		}
		
		if (preg_match('#^/password#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/login#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/logout#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/aviso#', $request->query)) {
			return false;
		}
		
		if ($request->user->type == 'a') {
			$filter = 'alumno=1';
		} else if ($request->user->type == 'm') {
			$filter = 'maestro=1';
		}
		$avisos = Gatuf::factory ('Pato_Aviso')->getList (array ('filter' => $filter));
		$leidos = $request->user->get_avisos_list (array ('filter' => $filter));
		
		if (count ($avisos) != count ($leidos)) {
			/* Hay un aviso pendiente por leer, obligarlo a leerlo */
			foreach ($avisos as $aviso) {
				$leido = false;
				foreach ($leidos as $l) {
					if ($l->id == $aviso->id) {
						$leido = true;
						break;
					}
				}
				
				if (!$leido) {
					/* Redirigir */
					$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Aviso::leer', array ($aviso->id), array ('_redirect_after' => $request->uri));
			
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
			}
		}
		
		return false;
	}
}

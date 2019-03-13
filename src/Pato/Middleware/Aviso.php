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
		
		$avisos = $request->user->get_avisos_list ();
		
		if (count ($avisos) > 0) {
			/* Hay un aviso pendiente por leer, obligarlo a leerlo */
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Aviso::leer', array ($avisos[0]->id), array ('_redirect_after' => $request->uri));
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return false;
	}
}

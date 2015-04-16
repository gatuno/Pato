<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Aviso {
	public $leer_precond = array ('Gatuf_Precondition::loginRequired');
	public function leer ($request, $match) {
		$aviso = new Pato_Aviso ();
		
		if ($aviso->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			if($request->user->type == 'a'){
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($request->user->login));
			} else {
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($request->user->login));
			}
		}
		
		if ($request->method == 'POST') {
			$aviso->setAssoc ($request->user);
			
			return new Gatuf_HTTP_Response_Redirect ($success_url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/aviso/leer.html',
		                                         array ('page_title' => 'Aviso importante',
		                                                'aviso' => $aviso,
		                                                '_redirect_after' => $success_url),
		                                         $request);
	}
}

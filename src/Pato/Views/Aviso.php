<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Aviso {
	public $leer_precond = array ('Gatuf_Precondition::loginRequired');
	public function leer ($request, $match) {
		$aviso_tabla = Gatuf::factory ('Pato_Aviso')->getSqlTable ();
		$sql = new Gatuf_SQL ($aviso_tabla.'.id = %s', $match[1]);
		
		$avisos = $request->user->get_avisos_list (array ('filter' => $sql->gen ()));
		
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Index::index');
		}
		
		if (count ($avisos) == 0) {
			return new Gatuf_HTTP_Response_Redirect ($success_url);
		}
		
		$aviso = $avisos[0];
		$real_aviso = Gatuf::factory ($avisos[0]->klass);
		
		if ($request->method == 'POST') {
			$result = $real_aviso->post ($request, $success_url, $aviso);
		} else {
			$result = $real_aviso->render ($request, $success_url, $aviso);
		}
		
		if ($result === true) {
			$request->user->delAssoc ($aviso);
			
			return new Gatuf_HTTP_Response_Redirect ($success_url);
		}
		
		return $result;
	}
}

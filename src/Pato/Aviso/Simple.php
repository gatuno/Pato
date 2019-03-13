<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Aviso_Simple {
	public function render ($request, $success_url, $aviso) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/aviso/leer.html',
		                                         array ('page_title' => 'Aviso importante',
		                                                'titulo' => $aviso->data['titulo'],
		                                                'texto' => $aviso->data['texto'],
		                                                '_redirect_after' => $success_url),
		                                         $request);
	}
	
	public function post ($request, $success_url, $data) {
		return true;
	}
}

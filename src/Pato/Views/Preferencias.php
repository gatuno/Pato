<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Preferencias {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/index.html',
		                                         array ('page_title' => 'Preferencias'),
		                                         $request);
	}
	
	public $cambiaFolio_precond = array ('Gatuf_Precondition::cambiaFolio');
	public function cambiarFolio ($request, $match) {
		$folio = $request->session->getData ('numero_folio', 1);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_EstablecerFolio ($request->POST, array ('numero' => $folio));
			
			if ($form->isValid ()) {
				$folio = $form->save ();
				
				$request->session->setData ('numero_folio', $folio);
				$request->user->setMessage (1, 'Número de folio cambiado a: '.$folio);
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Preferencias::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_EstablecerFolio (null, array ('numero' => $folio));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/preferencias/folio.html',
		                                         array ('page_title' => 'Cambiar número de folio',
		                                                'form' => $form,
		                                                'folio' => $folio),
		                                         $request);
	}
}

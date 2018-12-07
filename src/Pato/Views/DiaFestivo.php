<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_DiaFestivo {
	public $agregar_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregar ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if (false === ($calendario->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('cal' => $calendario);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_DiaFestivo_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$dia = $form->save ();
				
				//Gatuf_Log::info (sprintf ('La materia %s ha sido creada por el usuario %s (%s)', $materia->clave, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::ver', array ($calendario->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_DiaFestivo_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/agregar_dia_festivo.html',
		                                         array ('page_title' => 'Calendarios - Agregar día festivo',
		                                                'calendario' => $calendario,
		                                                'form' => $form),
		                                         $request);
	}
}

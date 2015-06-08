<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Admision_Views_Admitir {
	public $index_precond = array ('Pato_Precondition::coordinadorRequired');
	public function index ($request, $match) {
		/* Elegir una convocatoria */
		$convocatorias = Gatuf::factory ('Admision_Convocatoria')->getList ();
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/index.html',
		                                         array('page_title' => 'Admitir alumnos',
		                                               'convocatorias' => $convocatorias),
                                                 $request);
	}
	
	public $admitir_precond = array ('Pato_Precondition::coordinadorRequired');
	public function admitir ($request, $match) {
		$convocatoria = new Admision_Convocatoria ();
		
		if (false === ($convocatoria->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$cupos = array ();
		foreach ($convocatoria->get_admision_cupocarrera_list () as $cupo) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$cupo->carrera)) {
				$cupos[] = $cupo;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/carrera.html',
		                                         array('page_title' => 'Admitir alumnos para la convocatoria '.$convocatoria->descripcion,
		                                               'convocatoria' => $convocatoria,
		                                               'cupos' => $cupos),
                                                 $request);
	}
	
	public $admitirCarrera_precond = array ('Pato_Precondition::coordinadorRequired');
	public function admitirCarrera ($request, $match) {
		$cupo_carrera = new Admision_CupoCarrera ();
		
		if (false === ($cupo_carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->hasPerm ('Patricia.coordinador.'.$cupo_carrera->carrera)) {
			return new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		$alumnos = $cupo_carrera->get_alumnos_list();
		
		$extra = array ('alumnos' => $alumnos, 'cupo_carrera' => $cupo_carrera);
		if ($request->method == 'POST') {
			$form = new Admision_Form_Admitir_Carrera ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
				
				$request->user->setMessage (1, 'Sus selecciones han sido guardadas');
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Admitir::admitir', $cupo_carrera->convocatoria);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Admitir_Carrera (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/por_carrera.html',
		                                         array('page_title' => 'Admitir alumnos',
		                                               'convocatoria' => $cupo_carrera->get_convocatoria (),
		                                               'cupo_carrera' => $cupo_carrera,
		                                               'alumnos' => $alumnos,
		                                               'form' => $form),
                                                 $request);
	}
	
	public $verAspirante_precond = array ('Pato_Precondition::coordinadorRequired');
	public function verAspirante ($request, $match) {
		$aspirante = new Admision_Aspirante ();
		
		if (false === ($aspirante->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$cupo_carrera = $aspirante->get_aspiracion ();
		
		if (!$request->user->hasPerm ('Patricia.coordinador.'.$cupo_carrera->carrera)) {
			return new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/admitir/ver_popup.html',
		                                         array('page_title' => 'Aspirante '.$aspirante->id,
		                                               'aspirante' => $aspirante),
                                                 $request);
	}
}

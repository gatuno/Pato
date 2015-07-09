<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Reportes_Alumnos {
	public $ingreso_precond = array ('Gatuf_Precondition::adminRequired');
	public function ingreso ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$calendario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Alumnos::ingresoReporte', $calendario->clave);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Seleccionar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/alumnos/ingreso.html',
		                                         array('page_title' => 'Reporte',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $ingresoReporte_precond = array ('Gatuf_Precondition::adminRequired');
	public function ingresoReporte ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if (false === ($calendario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$sql = new Gatuf_SQL ('ingreso=%s', $calendario->clave);
		
		$inscripciones = Gatuf::factory ('Pato_Inscripcion')->getList (array ('filter' => $sql->gen ()));
		
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		$conteo = array ();
		foreach ($carreras as $c) {
			$conteo[$c->clave] = array ('ac' => array ('M' => 0, 'V' => 0), 'b2' => array ('M' => 0, 'V' => 0), 'o' => array ('M' => 0, 'V' => 0));
		}
		
		foreach ($inscripciones as $i) {
			$estatus = $i->get_current_estatus ();
			
			if ($estatus->estatus == 'AC') {
				$conteo[$i->carrera]['ac'][$i->turno]++;
			} else if ($estatus->estatus == 'B2') {
				$conteo[$i->carrera]['b2'][$i->turno]++;
			} else {
				$conteo[$i->carrera]['o'][$i->turno]++;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/alumnos/ingreso_reporte.html',
		                                         array('page_title' => 'Reporte',
		                                               'conteo' => $conteo,
		                                               'carreras' => $carreras),
                                                 $request);
	}
}

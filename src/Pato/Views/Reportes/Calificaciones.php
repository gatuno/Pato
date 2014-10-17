<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Reportes_Calificaciones {
	public $subidaTarde_precond = array ('Gatuf_Precondition::adminRequired');
	public function subidaTarde ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarEvaluacion ($request->POST);
			
			if ($form->isValid ()) {
				$eval = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Calificaciones::subidaTardeReporte', array ($eval->id));
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarEvaluacion (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/calificaciones/subida-tarde.html',
		                                         array('page_title' => 'Reporte maestros que no subieron calificaciones',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $subidaTardeReporte_precond = array ('Gatuf_Precondition::adminRequired');
	public function subidaTardeReporte ($request, $match) {
		$eval = new Pato_Evaluacion ();
		
		if (false === ($eval->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$incompletos = array ();
		
		/* Listar todas las materias que tienen esa forma de evaluación */
		$pors = $eval->get_pato_porcentaje_list ();
		$materia = new Pato_Materia ();
		
		$sql = new Gatuf_SQL ('evaluacion=%s', $eval->id);
		$where = $sql->gen ();
		
		foreach ($pors as $p) {
			$materia->get ($p->materia);
			
			/* Recuperar todas las secciones de esta materia */
			$secciones = $materia->get_pato_seccion_list ();
			
			foreach ($secciones as $sec) {
				/* Contabilizar el total de alumnos */
				$total = $sec->get_alumnos_list (array ('count' => true));
				
				/* Contabilizar cuantas boletas de esta forma de evaluación hay */
				$boletas = $sec->get_pato_boleta_list (array ('filter' => $where, 'count' => true));
				
				if ($boletas < $total) {
					$i = new stdClass ();
					$i->nrc = $sec;
					$i->total = $total;
					$i->calif = $boletas;
					$i->faltantes = $total - $boletas;
					
					$incompletos[] = $i;
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/calificaciones/subida-tarde-reporte.html',
		                                         array('page_title' => 'Reporte maestros que no subieron calificaciones',
		                                               'eval' => $eval,
		                                               'incompletos' => $incompletos),
                                                 $request);
	}
}

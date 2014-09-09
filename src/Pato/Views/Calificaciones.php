<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Calificaciones {
	
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/index.html',
		                                         array('page_title' => 'Calificaciones'),
                                                 $request);
	}
	
	public $aKardex_precond = array ('Gatuf_Precondition::adminRequired');
	public function aKardex ($request, $match) {
		$extra = array ('cal_activo' => $request->session->getData ('CAL_ACTIVO', ''));
		
		if ($request->method == 'POST') {
			/* El formulario viene de regreso */
			$form = new Pato_Form_Calificaciones_AKardex ($request->POST, $extra);
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$calendario = new Pato_Calendario ($data['cal']);
				$gpe = new Pato_GPE ($data['gpe']);
				
				/* Hacer cambio de calendario */
				$request->session->setData ('CAL_ACTIVO', $calendario->clave);
				$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
				
				/* Preparar el join SQL */
				$eval_t = Gatuf::factory ('Pato_Evaluacion')->getSqlTable ();
				$porc_model = new Pato_Porcentaje ();
				$porc_t = $porc_model->getSqlTable ();
				$porc_model->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$eval_t.' ON '.$eval_t.'.id=evaluacion');
				
				/* Recorrer cada sección de este calendario */
				$secciones = Gatuf::factory ('Pato_Seccion')->getList ();
				foreach ($secciones as $seccion) {
					/* Conseguir todos los porcentajes de esta sección */
					$materia = $seccion->get_materia ();
					$sql = new Gatuf_SQL ('materia=%s AND grupo=%s', array ($seccion->materia, $gpe->id));
					
					$porcentajes = $porc_model->getList (array ('view' => 'join_materia', 'filter' => $sql->gen ()));
					
					if (count ($porcentajes) == 0) {
						/* Si no tiene porcentajes, no podemos generar una calificación */
						continue;
					}
					
					/* Recorrer cada alumno */
					foreach ($seccion->get_alumnos_list () as $alumno) {
						/* Si el alumno ya tiene una calificación en kardex de este calendario y materia, omitir */
						$sql = new Gatuf_SQL ('materia=%s AND calendario=%s AND gpe=%s', array ($seccion->materia, $calendario->clave, $gpe->id));
						
						$kardxs = $alumno->get_kardex_list (array ('filter' => $sql->gen ()));
						if (count ($kardxs) != 0) {
							/* Este alumno YA tiene registrada una calificación en kardex, omitir */
							continue;
						}
						
						$suma = 0;
						foreach ($porcentajes as $porcentaje) {
							/* Tratar de conseguir la boleta */
							$sql = new Gatuf_SQL ('nrc=%s AND evaluacion=%s', array ($seccion->nrc, $porcentaje->evaluacion));
							
							$boleta = $alumno->get_boleta_list (array ('filter' => $sql->gen ()));
							
							if (count ($boleta) != 0) {
								$p = ($boleta[0]->calificacion * $porcentaje->porcentaje) / 100;
								$suma += $p;
							}
						}
						
						/* Tengo el total de este alumno */
						$kardex = new Pato_Kardex ();
						$kardex->alumno = $alumno;
						$kardex->materia = $materia;
						$kardex->nrc = $seccion->nrc;
						$kardex->calendario = $calendario;
						$kardex->gpe = $gpe;
						$kardex->calificacion = $suma;
						$kardex->aprobada = ($suma >= 7);
						
						$kardex->create ();
					}
				}
				
				return new Gatuf_HTTP_Response ('OK');
			}
		} else {
			$form = new Pato_Form_Calificaciones_AKardex (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/akardex.html',
		                                         array('page_title' => 'Convertir a Kardex',
		                                               'form' => $form),
                                                 $request);
	}
}

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
				$request->user->setMessage (1, 'El calendario ha sido cambiado a '.$calendario->descripcion);
				$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
				
				/* Preparar el join SQL */
				$eval_t = Gatuf::factory ('Pato_Evaluacion')->getSqlTable ();
				$porc_model = new Pato_Porcentaje ();
				$porc_t = $porc_model->getSqlTable ();
				$porc_model->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$eval_t.' ON '.$eval_t.'.id=evaluacion');
				
				$totales = array ('generados' => 0, 'enkardex' => 0);
				
				$secs = str_split ($gpe->secciones);
				$query = array ();
				$values = array ();
				foreach ($secs as $s) {
					$query[] = 'seccion LIKE %s';
					$values[] = $s.'%';
				}
				
				$sql = new Gatuf_SQL (implode (' OR ', $query), $values);
				
				/* Recorrer cada sección de este calendario */
				$secciones = Gatuf::factory ('Pato_Seccion')->getList (array ('filter' => $sql->gen ()));
				
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
						
						$kardxs = $alumno->get_kardex_list (array ('count' => true, 'filter' => $sql->gen ()));
						if ($kardxs != 0) {
							/* Este alumno YA tiene registrada una calificación en kardex, omitir */
							$totales['enkardex']++;
							continue;
						}
						
						/* Si el alumno ya tiene una calificación en kardex aprobatoria, omitir */
						$sql = new Gatuf_SQL ('materia=%s AND aprobada=1', array ($seccion->materia));
						
						$kardxs = $alumno->get_kardex_list (array ('count' => true, 'filter' => $sql->gen ()));
						if ($kardxs != 0) {
							/* Este alumno YA tiene registrada una calificación en kardex, omitir */
							$totales['enkardex']++;
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
						$totales['generados']++;
					}
				}
				
				return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/akardex-reporte.html',
						                                 array('page_title' => 'Convertir a Kardex - Completo',
						                                       'calendario' => $calendario,
						                                       'gpe' => $gpe,
						                                       'total' => $totales),
				                                         $request);
			}
		} else {
			$form = new Pato_Form_Calificaciones_AKardex (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/akardex.html',
		                                         array('page_title' => 'Convertir a Kardex',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $levantarKardex_precond = array ('Gatuf_Precondition::adminRequired');
	public function levantarKardex ($request, $match) {
		$extra = array ('cal_activo' => $request->session->getData ('CAL_ACTIVO', ''));
		
		if (isset ($request->GET['materia'])) $extra['materia'] = $request->GET['materia'];
		if (isset ($request->GET['gpe'])) $extra['gpe'] = $request->GET['gpe'];
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calificaciones_NuevaKardex ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$kardex = $form->save ();
				
				$request->user->setMessage (1, 'Calificación en Kardex creada correctamente para el alumno '.$kardex->alumno.' en la materia '.$kardex->materia);
				Gatuf_Log::info (sprintf ('Se levantó una calificación directa en Kardex al alumno %s (Kardex ID: %s), por el usuario %s', $kardex->alumno, $kardex->id, $request->user->codigo));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calificaciones::levantarKardex', array (), array ('materia' => $kardex->materia, 'gpe' => $kardex->gpe), false);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calificaciones_NuevaKardex (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/levantar-kardex.html',
		                                         array('page_title' => 'Levantar calificación en Kardex',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $aKardexSelectivo_precond = array ('Gatuf_Precondition::adminRequired');
	public function aKardexSelectivo ($request, $match) {
		$extra = array ('cal_activo' => $request->session->getData ('CAL_ACTIVO', ''));
		
		if ($request->method == 'POST') {
			/* El formulario viene de regreso */
			$form = new Pato_Form_Calificaciones_AKardexSelectivo ($request->POST, $extra);
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$calendario = new Pato_Calendario ($data['cal']);
				$gpe = new Pato_GPE ($data['gpe']);
				
				/* Hacer cambio de calendario */
				$request->session->setData ('CAL_ACTIVO', $calendario->clave);
				$request->user->setMessage (1, 'El calendario ha sido cambiado a '.$calendario->descripcion);
				$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
				
				/* Preparar el join SQL */
				$eval_t = Gatuf::factory ('Pato_Evaluacion')->getSqlTable ();
				$porc_model = new Pato_Porcentaje ();
				$porc_t = $porc_model->getSqlTable ();
				$porc_model->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$eval_t.' ON '.$eval_t.'.id=evaluacion');
				
				$totales = array ('generados' => 0, 'enkardex' => 0);
				
				$secs = str_split ($gpe->secciones);
				$query = array ();
				$values = array ();
				foreach ($secs as $s) {
					$query[] = 'seccion LIKE %s';
					$values[] = $s.'%';
				}
				
				$sql_sec = new Gatuf_SQL (implode (' OR ', $query), $values);
				
				$materia = new Pato_Materia ();
				/* Recorrer cada sección de las materias seleccionadas */
				foreach ($data['materias'] as $m_clave) {
					$materia->get ($m_clave);
					$secciones = $materia->get_pato_seccion_list (array ('filter' => $sql_sec->gen ()));
					foreach ($secciones as $seccion) {
						/* Conseguir todos los porcentajes de esta sección */
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
						
							$kardxs = $alumno->get_kardex_list (array ('count' => true, 'filter' => $sql->gen ()));
							if ($kardxs != 0) {
								/* Este alumno YA tiene registrada una calificación en kardex, omitir */
								$totales['enkardex']++;
								continue;
							}
						
							/* Si el alumno ya tiene una calificación en kardex aprobatoria, omitir */
							$sql = new Gatuf_SQL ('materia=%s AND aprobada=1', array ($seccion->materia));
						
							$kardxs = $alumno->get_kardex_list (array ('count' => true, 'filter' => $sql->gen ()));
							if ($kardxs != 0) {
								/* Este alumno YA tiene registrada una calificación en kardex, omitir */
								$totales['enkardex']++;
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
							$totales['generados']++;
						}
					}
				}
				
				return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/akardex-reporte.html',
						                                 array('page_title' => 'Convertir a Kardex - Completo',
						                                       'calendario' => $calendario,
						                                       'gpe' => $gpe,
						                                       'total' => $totales),
				                                         $request);
			}
		} else {
			$form = new Pato_Form_Calificaciones_AKardexSelectivo (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/akardex.html',
		                                         array('page_title' => 'Convertir a Kardex - Selectivo por materia',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $correccionBuscar_precond = array ('Gatuf_Precondition::adminRequired');
	public function correccionBuscar ($request, $match) {
		$sql = new Gatuf_SQL ();
		
		$form = new Pato_Form_Calificaciones_Buscar ($request->GET);
		
		if ($form->isValid ()) {
			$data = $form->save ();
		} else {
			$data = array ('materia' => 'NULL', 'alumno' => '');
		}
		
		/* Si hay materia, aplicar filtro */
		if ($data['materia'] != 'NULL') {
			$sql->Q ('materia=%s', $data['materia']);
		}
		
		if ($data['alumno'] != '') {
			$sql->Q ('alumno=%s', $data['alumno']);
		}
		
		$where = $sql->gen ();
		
		if ($where == '') {
			$resultados = array ();
			$form = new Pato_Form_Calificaciones_Buscar (null);
		} else {
			$resultados = Gatuf::factory ('Pato_Kardex')->getList (array ('filter' => $where));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/correccion-buscar.html',
		                                         array('page_title' => 'Corrección a Kardex',
		                                               'form' => $form,
		                                               'where' => $where,
		                                               'resultados' => $resultados),
                                                 $request);
	}
	
	public $correccionKardex_precond = array ('Gatuf_Precondition::adminRequired');
	public function correccionKardex ($request, $match) {
		$kardex = new Pato_Kardex ();
		
		if ($kardex->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('kardex' => $kardex);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calificaciones_Correccion ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				/* Crear un log para esta corrección */
				$log = new Pato_Log_Kardex ();
				$log->usuario = $request->user;
				$log->kardex = $kardex;
				$log->vieja = $kardex->calificacion;
				$kardex->calificacion = $data['calificacion'];
				$kardex->aprobada = ($kardex->calificacion >= 7);
				$log->nueva = $kardex->calificacion;
				
				$log->create ();
				
				$kardex->update ();
				
				$request->user->setMessage (1, 'Calificación corregida en Kardex');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calificaciones::correccionBuscar');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calificaciones_Correccion (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calificaciones/correccion.html',
		                                         array('page_title' => 'Corrección a Kardex',
		                                               'form' => $form,
		                                               'kardex' => $kardex),
                                                 $request);
	}
}

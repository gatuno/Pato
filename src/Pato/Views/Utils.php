<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Utils {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/index.html',
		                                         array('page_title' => 'Utilerias varias'),
                                                 $request);
	}
	
	public $loteBoletas_precond = array ('Gatuf_Precondition::adminRequired');
	public function loteBoletas ($request, $match) {
		$carreras = Gatuf::factory ('Pato_Carrera')->getList ();
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/lote-boletas.html',
		                                         array('page_title' => 'Imprimir boletas en lote',
                                                       'carreras' => $carreras),
                                                 $request);
	}
	
	public $loteBoletaCarrera_precond = array ('Gatuf_Precondition::adminRequired');
	public function loteBoletaCarrera ($request, $match) {
		$carrera = new Pato_Carrera ();
		
		if (false === $carrera->get ($match[1])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Alumno_Boleta ('P', 'mm', 'Letter');
		
		$inscripciones = $carrera->get_pato_inscripcion_list (array ('filter' => 'egreso IS NULL'));
		
		foreach ($inscripciones as $ins) {
			$estatus = $ins->get_estatus ();
			
			if (!$estatus->activo) continue;
			
			$alumno = $ins->get_alumno ();
			
			$pdf->renderBoleta ($alumno);
		}
		
		$nombre = 'boletas_'.$carrera->clave.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $altasBajasMasivas_precond = array ('Gatuf_Precondition::adminRequired');
	public function altasBajasMasivas ($request, $match) {
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Utils_AltasBajas ($request->POST);
			
			if ($form->isValid ()) {
				$cambios = $form->save ();
				
				$horario_check = $cambios['opc']['horario'];
				
				$seccion = new Pato_Seccion ();
				$alumno = new Pato_Alumno ();
				
				/* Realizar primero las bajas */
				foreach ($cambios['bajas'] as $cambio) {
					if ($seccion->get ($cambio[0]) === false) {
						$request->user->setMessage (3, 'El nrc '.$cambio[0].' no existe');
						continue;
					}
					
					if ($alumno->get ($cambio[1]) === false) {
						$request->user->setMessage (3, 'El alumno '.$cambio[1].' no existe');
						continue;
					}
					$sql = new Gatuf_SQL ('pato_alumno_codigo=%s', $alumno->codigo);
					
					$alumnos = $seccion->get_alumnos_list (array ('filter' => $sql->gen ()));
					
					if (count ($alumnos) == 0) {
						$request->user->setMessage (2, 'El alumno '.$alumno->codigo.' no está registrado en el nrc '.$seccion->nrc);
						continue;
					}
					
					/* Borrar las calificaciones y asistencias
					 * TODO: Convertir esto en un TRIGGER */
					$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
			
					$asistencias = Gatuf::factory ('Pato_Asistencia')->getList (array ('filter' => $sql->gen ()));
					foreach ($asistencias as $asis) {
						$asis->delete ();
					}
			
					$boletas = Gatuf::factory ('Pato_Boleta')->getList (array ('filter' => $sql->gen ()));
					foreach ($boletas as $b) {
						$b->delete ();
					}
					
					$alumno->delAssoc ($seccion);
					Gatuf_Log::info (sprintf ('El usuario %s ha desmatriculado al alumno %s del NRC %s', $request->user->codigo, $alumno->codigo, $seccion->nrc));
					$request->user->setMessage (1, 'El alumno '.$alumno->codigo.' fué desmatriculado del nrc '.$seccion->nrc);
				}
				
				Gatuf::loadFunction ('Pato_Procedimiento_matricular');
				/* Realizar las altas */
				foreach ($cambios['altas'] as $cambio) {
					if ($seccion->get ($cambio[0]) === false) {
						$request->user->setMessage (3, 'El nrc '.$cambio[0].' no existe');
						continue;
					}
					
					if ($alumno->get ($cambio[1]) === false) {
						$request->user->setMessage (3, 'El alumno '.$cambio[1].' no existe');
						continue;
					}
					
					$resp = Pato_Procedimiento_matricular ($seccion, $alumno, $horario_check, false);
				
					if ($resp !== true) {
						$request->user->setMessage (2, 'El alumno ('.$alumno->codigo.') no se pudo matricular en el NRC '.$seccion->nrc.' por la siguiente razón: '.$resp);
					} else {
						Gatuf_Log::info (sprintf ('El usuario %s ha matriculado al alumno %s en el NRC %s', $request->user->codigo, $alumno->codigo, $seccion->nrc));
						$request->user->setMessage (1, 'Alumno '.$alumno->codigo.' matriculado en el NRC '.$seccion->nrc);
					}
				}
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Utils::altasBajasMasivas');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Utils_AltasBajas (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/altas-bajas.html',
		                                         array('page_title' => 'Altas y Bajas masivas',
                                                       'form' => $form),
                                                 $request);
	}
	
	public $cambiarFechaEval_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarFechaEval ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Utils_ActualizarEval ($request->POST);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				/* Recoger todos los porcentajes de esta forma de evaluación y actualizarlos */
				$eval = new Pato_Evaluacion ($data['evaluacion']);
				
				$pors = $eval->get_pato_porcentaje_list ();
				
				foreach ($pors as $p) {
					$p->abierto = $data['abierto'];
					
					$p->apertura = $data['apertura'];
					$p->cierre = $data['cierre'];
					
					$p->update ();
				}
				
				$request->user->setMessage (1, 'Las fechas de la forma de evaluación '.$eval->descripcion.' han sido cambiadas correctamente');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Utils::cambiarFechaEval');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Utils_ActualizarEval (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/cambiar-fechas.html',
		                                         array('page_title' => 'Cambiar fechas de evaluación',
                                                       'form' => $form),
                                                 $request);
	}
	
	public $agregarPorcentaje_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarPorcentaje ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Utils_AgregarPorcentaje ($request->POST);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$gpe = new Pato_GPE ();
				$gpe->get($data['gpe']);
				
				$secs = str_split ($gpe->secciones);
				$query = array ();
				$values = array ();
				foreach ($secs as $s) {
					$query[] = 'seccion LIKE %s';
					$values[] = $s.'%';
				}
				
				$sql = new Gatuf_SQL ('('.implode (' OR ', $query).')', $values);
				/* Recorrer todas las secciones de la forma del grupo de evaluacion seleccionada */
				$secciones = Gatuf::factory ('Pato_Seccion')->getList (array ('filter' => $sql->gen()));
				$mats = array ();
				
				foreach ($secciones as $s) {
					$mats[$s->materia] = 1;
				}
				$sql = new Gatuf_SQL ('evaluacion=%s', $data['evaluacion']);
				$where = $sql->gen ();
				
				$evaluacion = new Pato_Evaluacion ($data['evaluacion']);
				
				/* Recorrer todas las materias */
				$materia = new Pato_Materia ();
				$total = 0;
				foreach ($mats as $m => $one) {
					$materia->get ($m);
					
					$ps = $materia->get_porcentajes_list (array ('filter' => $where));
					
					if (count ($ps) == 0) {
						/* Agregar un porcentaje a esta materia */
						$p = new Pato_Porcentaje ();
						$p->materia = $materia;
						$p->evaluacion = $evaluacion;
						
						$p->porcentaje = $data['porcentaje'];
						$p->abierto = $data['abierto'];
						$p->apertura = $data['apertura'];
						$p->cierre = $data['cierre'];
						
						$p->create ();
						$total++;
					}
				}
				
				$request->user->setMessage (1, 'Se ha agregado la forma de evaluación '.$evaluacion->descripcion.' a todas las materias con secciones activas. ('.$total.' agregados)');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Utils::agregarPorcentaje');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Utils_AgregarPorcentaje (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/agregar-porcentaje.html',
		                                         array('page_title' => 'Agregar forma de evaluacion a todas las materias',
                                                       'form' => $form),
                                                 $request);
	}
	
	public $cambiarPorcentaje_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarPorcentaje ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Utils_CambiarPorcentaje ($request->POST);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				/* Recorrer todas las secciones */
				$secciones = Gatuf::factory ('Pato_Seccion')->getList ();
				
				$eval = new Pato_Evaluacion ($data['evaluacion']);
				
				$pors = $eval->get_pato_porcentaje_list ();
				
				foreach ($pors as $p) {
					$p->porcentaje = $data['porcentaje'];
					
					$p->update ();
				}
				
				$request->user->setMessage (1, 'Los porcentajes de la forma de evaluación '.$eval->descripcion.' han sido cambiados a '.$data['porcentaje'].'%');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Utils::cambiarPorcentaje');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Utils_CambiarPorcentaje (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/cambiar-porcentaje.html',
		                                         array('page_title' => 'Cambiar porcentaje de forma masiva a una forma de evaluación',
                                                       'form' => $form),
                                                 $request);
	}
	
	public $generarAgendas_precond = array ('Gatuf_Precondition::adminRequired');
	public function generarAgendas ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Utils_Agenda ($request->POST);
			
			if ($form->isValid ()) {
				$data = $form->save ();
				
				$calendario = new Pato_Calendario ($data['calendario']);
				
				/* Forzar el cambio de calendario */
				$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
				
				$db = Pato_Calendario_getDBForCal ($calendario->clave);
				
				$alumno_model = new Pato_Alumno ();
				
				$a_actuales = $db->dbname.'.'.$db->pfx.'alumnos_actuales';
				
				$alumno_model->_a['views']['por_m']['join'] = sprintf ('RIGHT JOIN %s AS AA ON %s.codigo = AA.alumno', $a_actuales, $alumno_model->getSqlTable ());
				$carrera = null;
				if (!is_null ($data['carrera'])) {
					$carrera = new Pato_Carrera ($data['carrera']);
					$sql = new Gatuf_SQL ('AA.carrera = %s', $data['carrera']);
					$alumno_model->_a['views']['por_m']['where'] = $sql->gen ();
				}
				
				$alumnos = $alumno_model->getList (array ('view' => 'por_m'));
				
				$total = 0;
				/* Generar las agendas, o en su defecto, actualizar */
				foreach ($alumnos as $alumno) {
					$ins = $alumno->get_inscripcion_for_cal ($calendario);
					$estatus = $ins->get_current_estatus ();
					
					if (!$estatus->isActivo ()) continue;
					
					$total++;
					
					$agendas = $alumno->get_agenda_list ();
					
					if (count ($agendas) == 0) {
						/* Crearle la agenda */
						$agenda = new Pato_Agenda ();
						$agenda->alumno = $alumno;
						$agenda->inicio = $data['inicio'];
						$agenda->fin = $data['fin'];
						
						$agenda->create ();
					} else {
						/* Actualizar la agenda */
						$agendas[0]->inicio = $data['inicio'];
						$agendas[0]->fin = $data['fin'];
						
						$agendas[0]->update ();
					}
				}
				
				/* Aquí presentar el reporte */
				return Gatuf_Shortcuts_RenderToResponse ('pato/utils/generar-agendas-reporte.html',
				                                         array('page_title' => 'Generar agendas',
		                                                       'total' => $total,
		                                                       'carrera' => $carrera,
		                                                       'calendario' => $calendario),
		                                                 $request);
			}
		} else {
			$form = new Pato_Form_Utils_Agenda (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/generar-agendas.html',
		                                         array('page_title' => 'Generar agendas',
                                                       'form' => $form),
                                                 $request);
	}
	
	public $agregarPostal_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarPostal ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Utils_AgregarPostal ($request->POST);
			
			if ($form->isValid ()) {
				$postal = $form->save ();
				
				$request->user->setMessage (1, 'Código postal creado');
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Utils::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Utils_AgregarPostal (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/utils/agregar-postal.html',
		                                         array('page_title' => 'Agregar código postal',
                                                       'form' => $form),
                                                 $request);
	}
}

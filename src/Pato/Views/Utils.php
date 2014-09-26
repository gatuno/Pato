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
					$request->user->setMessage (1, 'El alumno '.$alumno->codigo.' fué desmatriculado del nrc '.$seccion->nrc);
				}
				
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
					
					$sql = new Gatuf_SQL ('pato_alumno_codigo=%s', $alumno->codigo);
					
					$alumnos = $seccion->get_alumnos_list (array ('filter' => $sql->gen ()));
					
					if (count ($alumnos) != 0) {
						$request->user->setMessage (2, 'El alumno '.$alumno->codigo.' ya está registrado en el nrc '.$seccion->nrc.', ignorando');
						continue;
					}
					
					$secciones = $alumno->get_grupos_list ();
					$found = false;
					
					/* Para acumular las horas que lleva ese alumno */
					$horas = array ();
					
					/* Recorrer todas las secciones que ya tiene matriculado este alumno */
					foreach ($secciones as $s) {
						if ($s->materia == $seccion->materia) {
							$request->user->setMessage (3, 'El alumno '.$alumno->codigo.' ya tiene matriculada una materia '.$seccion->materia.', por lo tanto, el NRC '.$seccion->nrc.' no se pudo dar de alta');
							$found = true;
							break;
						}
						if ($horario_check) {
							foreach ($s->get_pato_horario_list () as $h_al) {
								$horas[] = $h_al;
							}
						}
					}
					
					if ($found) continue;
					
					if ($horario_check) {
						$choque = false;
						foreach ($horas as $h_al) {
							foreach ($seccion->get_pato_horario_list () as $h_sec) {
								if (Pato_Horario::chocan ($h_al, $h_sec)) $choque = true;
							}
						}
						
						if ($choque) {
							$request->user->setMessage (2, 'El alumno '.$alumno->codigo.' tiene conflictos de horario al matricular el NRC '.$seccion->nrc);
							continue;
						}
					}
					
					$alumno->setAssoc ($seccion);
					$request->user->setMessage (1, 'Alumno '.$alumno->codigo.' matriculado en el NRC '.$seccion->nrc);
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
}

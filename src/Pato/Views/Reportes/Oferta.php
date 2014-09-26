<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Reportes_Oferta {
	public $matriculados_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculados ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$calendario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Oferta::matriculadosCalendario', $calendario->clave);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Seleccionar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/oferta/matriculados.html',
		                                         array('page_title' => 'Reporte matriculados',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $matriculadosCalendario_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculadosCalendario ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if ($calendario->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$total = 0;
		$matriculados = 0;
		
		foreach (Gatuf::factory ('Pato_Alumno')->getList () as $alumno) {
			$ins = $alumno->get_inscripcion_for_cal ($calendario);
			
			if ($ins == null) continue;
			
			$total++;
			
			$count_s = $alumno->get_grupos_list (array ('count' => true));
			
			if ($count_s == 0) {
				$no_mat[] = $alumno;
			} else {
				$matriculados++;
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/oferta/reporte-matriculados.html',
		                                         array('page_title' => 'Reporte matriculados para '.$calendario->descripcion,
		                                               'calendario' => $calendario,
		                                               'total' => $total,
		                                               'matriculados' => $matriculados),
                                                 $request);
	}
	
	public $matriculadosCalendarioODS_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculadosCalendarioODS ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if ($calendario->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$ods = new Gatuf_ODS ();
		
		$ods->addNewSheet ('Con alguna materia');
		$ods->addStringCell ('Con alguna materia', 1, 1, 'Código');
		$ods->addStringCell ('Con alguna materia', 1, 2, 'Nombre');
		$ods->addStringCell ('Con alguna materia', 1, 3, 'Carrera');
		$ods->addStringCell ('Con alguna materia', 1, 4, 'Materias registradas');
		$g = 2;
		
		$ods->addNewSheet ('Nada matriculado');
		$ods->addStringCell ('Nada matriculado', 1, 1, 'Código');
		$ods->addStringCell ('Nada matriculado', 1, 2, 'Nombre');
		$ods->addStringCell ('Nada matriculado', 1, 3, 'Carrera');
		$h = 2;
		
		foreach (Gatuf::factory ('Pato_Alumno')->getList () as $alumno) {
			$ins = $alumno->get_inscripcion_for_cal ($calendario);
			
			if ($ins == null) continue;
			
			$count_s = $alumno->get_grupos_list (array ('count' => true));
			
			if ($count_s == 0) {
				$ods->addStringCell ('Nada matriculado', $h, 1, $alumno->codigo);
				$ods->addStringCell ('Nada matriculado', $h, 2, $alumno->apellido.' '.$alumno->nombre);
				$ods->addStringCell ('Nada matriculado', $h, 3, $ins->carrera);
				$h++;
			} else {
				$ods->addStringCell ('Con alguna materia', $g, 1, $alumno->codigo);
				$ods->addStringCell ('Con alguna materia', $g, 2, $alumno->apellido.' '.$alumno->nombre);
				$ods->addStringCell ('Con alguna materia', $g, 3, $ins->carrera);
				$ods->addStringCell ('Con alguna materia', $g, 4, $count_s);
				$g++;
			}
		}
		
		$ods->construir_paquete ();
		return new Gatuf_HTTP_Response_File ($ods->nombre, 'Matriculados-'.$calendario->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
	
	public $maestrosActivos_precond = array ('Gatuf_Precondition::adminRequired');
	public function maestrosActivos ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$calendario = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Oferta::maestrosActivosCalendario', $calendario->clave);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Seleccionar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/oferta/maestros-activos.html',
		                                         array('page_title' => 'Reporte de maestros activos',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $maestrosActivosCalendario_precond = array ('Gatuf_Precondition::adminRequired');
	public function maestrosActivosCalendario ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if ($calendario->get ($match[1]) === false) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$ods = new Gatuf_ODS ();
		
		$ods->addNewSheet ('Maestros');
		$ods->addStringCell ('Maestros', 1, 1, 'Código');
		$ods->addStringCell ('Maestros', 1, 2, 'Nombre');
		$ods->addStringCell ('Maestros', 1, 3, 'Apellidos');
		$ods->addStringCell ('Maestros', 1, 4, 'Materias clave');
		$ods->addStringCell ('Maestros', 1, 5, 'NRCS');
		$ods->addStringCell ('Maestros', 1, 6, 'Materias');
		$g = 2;
		
		foreach (Gatuf::factory ('Pato_Maestro')->getList () as $maestro) {
			$secciones = $maestro->get_primario_list ();
			
			if ($secciones->count () == 0) continue;
			
			$ods->addStringCell ('Maestros', $g, 1, $maestro->codigo);
			$ods->addStringCell ('Maestros', $g, 2, $maestro->nombre);
			$ods->addStringCell ('Maestros', $g, 3, $maestro->apellido);
			
			$claves = array ();
			$materias = array ();
			$nrcs = array ();
			
			foreach ($secciones as $seccion) {
				$nrcs[] = $seccion->nrc;
				$claves[] = $seccion->materia.' '.$seccion->seccion;
				$materias[] = $seccion->get_materia ()->descripcion;
			}
			
			$materias = array_unique ($materias);
			$ods->addStringCell ('Maestros', $g, 4, implode (', ', $claves));
			$ods->addStringCell ('Maestros', $g, 5, implode (', ', $nrcs));
			$ods->addStringCell ('Maestros', $g, 6, implode (', ', $materias));
			
			$g++;
		}
		
		$ods->construir_paquete ();
		return new Gatuf_HTTP_Response_File ($ods->nombre, 'Maestros-activos-'.$calendario->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
}

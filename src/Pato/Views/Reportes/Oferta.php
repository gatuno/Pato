<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Reportes_Oferta {
	public $matriculadosCalendario_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculadosCalendario ($request, $match) {
		$calendario = $request->calendario;
		
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
		$calendario = $request->calendario;
		
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
	
	public $matriculadosMateriaIndex_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculadosMateriaIndex ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Materia_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$materia = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Oferta::matriculadosMateria', $materia->clave);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Materia_Seleccionar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/oferta/matriculados-sel-materia.html',
		                                         array('page_title' => 'Reporte matriculados por materia',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $matriculadosMateria_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculadosMateria ($request, $match) {
		$materia = new Pato_Materia ();
		
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$secciones = $materia->get_pato_seccion_list ();
		$total = array ();
		$suma = 0;
		
		foreach ($secciones as $s) {
			$alumnos = $s->get_alumnos_list (array ('count' => true));
			
			$total[$s->nrc] = $alumnos;
			$suma += $alumnos;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/oferta/reporte-matriculados-materia.html',
		                                         array('page_title' => 'Reporte matriculados para '.$request->calendario->descripcion.' en '.$materia->descripcion,
		                                               'materia' => $materia,
		                                               'total' => $total,
		                                               'suma' => $suma,
		                                               'secciones' => $secciones),
                                                 $request);
	}
	
	public $matriculadosMateriaODS_precond = array ('Gatuf_Precondition::adminRequired');
	public function matriculadosMateriaODS ($request, $match) {
		$materia = new Pato_Materia ();
		
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ods = new Gatuf_ODS ();
		
		$ods->addNewSheet ($materia->descripcion);
		$ods->addStringCell ($materia->descripcion, 1, 1, 'NRC');
		$ods->addStringCell ($materia->descripcion, 1, 2, 'Clave');
		$ods->addStringCell ($materia->descripcion, 1, 3, 'Materia');
		$ods->addStringCell ($materia->descripcion, 1, 4, 'Sección');
		$ods->addStringCell ($materia->descripcion, 1, 5, 'Profesor');
		$ods->addStringCell ($materia->descripcion, 1, 6, 'Código');
		$ods->addStringCell ($materia->descripcion, 1, 7, 'Alumno');
		
		$g = 2;
		
		$secciones = $materia->get_pato_seccion_list ();
		$total = array ();
		$suma = 0;
		
		foreach ($secciones as $s) {
			$alumnos = $s->get_alumnos_list ();
			
			foreach ($alumnos as $a) {
				$ods->addStringCell ($materia->descripcion, $g, 1, $s->nrc);
				$ods->addStringCell ($materia->descripcion, $g, 2, $materia->clave);
				$ods->addStringCell ($materia->descripcion, $g, 3, $materia->descripcion);
				$ods->addStringCell ($materia->descripcion, $g, 4, $s->seccion);
				$ods->addStringCell ($materia->descripcion, $g, 5, (string) $s->get_maestro ());
				$ods->addStringCell ($materia->descripcion, $g, 6, $a->codigo);
				$ods->addStringCell ($materia->descripcion, $g, 7, $a->apellido.' '.$a->nombre);
				$g++;
			}
		}
		
		$ods->construir_paquete ();
		return new Gatuf_HTTP_Response_File ($ods->nombre, 'Matriculados_'.$materia->clave.'_'.$request->calendario->clave.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
	
	public $maestrosActivos_precond = array ('Gatuf_Precondition::adminRequired');
	public function maestrosActivos ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarGPE ($request->POST);
			
			if ($form->isValid ()) {
				$gpe = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Reportes_Oferta::maestrosActivosCalendario', $gpe->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_SeleccionarGPE (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/oferta/maestros-activos.html',
		                                         array('page_title' => 'Reporte de maestros activos',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $maestrosActivosCalendario_precond = array ('Gatuf_Precondition::adminRequired');
	public function maestrosActivosCalendario ($request, $match) {
		$calendario = $request->calendario;
		$gpe = new Pato_GPE ();
		
		if (false === ($gpe->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ods = new Gatuf_ODS ();
		
		$ods->addNewSheet ('Maestros');
		$ods->addStringCell ('Maestros', 1, 1, 'Código');
		$ods->addStringCell ('Maestros', 1, 2, 'Nombre');
		$ods->addStringCell ('Maestros', 1, 3, 'Apellidos');
		$ods->addStringCell ('Maestros', 1, 4, 'Materias clave');
		$ods->addStringCell ('Maestros', 1, 5, 'NRCS');
		$ods->addStringCell ('Maestros', 1, 6, 'Materias');
		$g = 2;
		
		$secs = str_split ($gpe->secciones);
		$query = array ();
		$values = array ();
		foreach ($secs as $s) {
			$query[] = 'seccion LIKE %s';
			$values[] = $s.'%';
		}
		
		$sql = new Gatuf_SQL ('('.implode (' OR ', $query).')', $values);
		
		foreach (Gatuf::factory ('Pato_Maestro')->getList () as $maestro) {
			$secciones = $maestro->get_primario_list (array ('filter' => $sql->gen ()));
			
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
		return new Gatuf_HTTP_Response_File ($ods->nombre, 'maestros-activos_'.$calendario->clave.'_'.$gpe->descripcion.'.ods', 'application/vnd.oasis.opendocument.spreadsheet', true);
	}
}

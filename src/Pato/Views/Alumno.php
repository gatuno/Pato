<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Alumno {
	public function index ($request, $match) {
		$alumno =  new Pato_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->action = array ('Pato_Views_Alumno::index');
		$pag->summary = 'Lista de los alumnos';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron alumnos';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/index.html',
		                                         array('page_title' => 'Alumnos',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public $agregarAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarAlumno ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agregar-alumno.html',
		                                         array ('page_title' => 'Nuevo alumno',
		                                                'form' => $form),
		                                         $request);
	}
	
	
	public function verAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-alumno.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno),
                                                 $request);
	}
	
	public function verInscripciones ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$inscripciones = $alumno->get_inscripciones_list (array ('order' => 'ingreso DESC'));
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-inscripciones.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'inscripciones' => $inscripciones),
                                                 $request);
	}
	
	public function verGrupos ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$secciones = $alumno->get_grupos_list(array ('view' => 'paginador'));
		
		$especiales = array (3 => 'IN', 2 => 'SD');
		
		/* Conseguir todas las formas de evaluación para todas las secciones */
		$porc_t = Gatuf::factory ('Pato_Porcentaje')->getSqlTable ();
		$eval = new Pato_Evaluacion ();
		$eval_t = $eval->getSqlTable ();
		$eval->_a['views']['join_materia'] = array ('join' => 'LEFT JOIN '.$porc_t.' ON '.$eval_t.'.id=evaluacion');
		
		$evaluaciones = array ();
		$asistencias = array ();
		$boleta = array ();
		$sql_al = new Gatuf_SQL ('alumno=%s', $alumno->codigo);
		foreach ($secciones as $seccion) {
			$sql = new Gatuf_SQL ('materia=%s', $seccion->materia);
			$evaluaciones[$seccion->nrc] = $eval->getList (array ('view' => 'join_materia', 'filter' => $sql->gen ()));
			$t_as = $seccion->get_pato_asistencia_list (array ('filter' => $sql_al->gen ()));
			
			if (count ($t_as) == 0) {
				$asistencias[$seccion->nrc] = null;
			} else {
				$asistencias[$seccion->nrc] = $t_as[0];
			}
			$boleta[$seccion->nrc] = array ();
			foreach ($seccion->get_pato_boleta_list (array ('filter' => $sql_al->gen ())) as $b) {
				$boleta[$seccion->nrc][$b->evaluacion] = $b->calificacion;
			}
		}
		
		/* Recoger las asistencias */
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-grupos.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'secciones' => $secciones,
		                                               'evals' => $evaluaciones,
		                                               'boleta' => $boleta,
		                                               'especial' => $especiales,
		                                               'asistencias' => $asistencias),
                                                 $request);
	}

	public $actualizarAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === $alumno->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/edit-alumno.html',
		                                         array ('page_title' => 'Actualizar alumno',
		                                                'alumno' => $alumno,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $buscarJSON_precond = array ('Gatuf_Precondition::adminRequired');
	public function buscarJSON ($request, $match) {
		if (!isset ($request->GET['term'])) {
			return new Gatuf_HTTP_Response_Json (array ());
		}
		
		$bus = '%'.$request->GET['term'].'%';
		
		$sql = new Gatuf_SQL ('nombre LIKE %s OR apellido LIKE %s or codigo LIKE %s', array ($bus, $bus, $bus));
		$alumnos = Gatuf::factory ('Pato_Alumno')->getList (array ('filter' => $sql->gen ()));
		
		$response = array ();
		foreach ($alumnos as $alumno) {
			$o = new stdClass();
			$o->value = (string) $alumno->codigo;
			$o->label = (string) $alumno;
			
			$response[] = $o;
		}
		
		return new Gatuf_HTTP_Response_Json ($response);
	}
	
	public $verFormatos_precond = array ('Gatuf_Precondition::adminRequired');
	public function verFormatos ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/formatos.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno),
		                                         $request);
	}
	
	public $boleta_precond = array ('Gatuf_Precondition::adminRequired');
	public function boleta ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$pdf = new Pato_PDF_Alumno_Boleta ('P', 'mm', 'Letter');
		
		$pdf->renderBoleta ($alumno);
		
		$pdf->Close ();
		
		$nombre = 'boleta_'.$alumno->codigo.'.pdf';
		$pdf->Output (Gatuf::config ('tmp_folder').'/'.$nombre, 'F');
		
		return new Gatuf_HTTP_Response_File (Gatuf::config ('tmp_folder').'/'.$nombre, $nombre, 'application/pdf', true);
	}
	
	public $agenda_precond = array ('Gatuf_Precondition::loginRequired');
	public function agenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator) {
			if ($request->user->login != $alumno->codigo) {
				throw new Gatuf_HTTP_Error404 ();
			}
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			$agenda = null;
		} else {
			$agenda = $list[0];
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'agenda' => $agenda),
		                                         $request);
	}
}

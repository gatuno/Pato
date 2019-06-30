<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Planeacion_Reportes {
	public $index_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.reportes_planeacion'));
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/reportes/index.html',
		                                          array ('page_title' => 'Planeación'),
		                                          $request);
	}
	
	public $reportePorMaestro_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.reportes_planeacion'));
	public function reportePorMaestro ($request, $match) {
		$maestro = new Pato_Maestro ();
		
		if (false === ($maestro->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$secciones_count = $maestro->get_primario_list (array ('view' => 'mats_cant'));
		
		$materias = array ();
		$secciones = array ();
		foreach ($secciones_count as $s) {
			$m = $s->get_materia ();
			$m->cant_grupos = $s->cant_grupos;
			
			$sql_mm = new Gatuf_SQL ('materia=%s AND maestro=%s', array ($m->clave, $maestro->codigo));
			$secciones[$m->clave] = Gatuf::factory ('Pato_Seccion')->getList (array ('filter' => $sql_mm->gen ()));
			$materias[] = $m;
		}
		
		$totals = array ();
		$tema_model = new Pato_Planeacion_Tema ();
		$unidad_model = new Pato_Planeacion_Unidad ();
		$seguimiento_model = new Pato_Planeacion_Seguimiento ();
		
		$u_tabla = $unidad_model->getSqlTable ();
		$t_tabla = $tema_model->getSqlTable ();
		$s_tabla = $seguimiento_model->getSqlTable ();
		
		$tema_model->_a['views']['por_mm'] = array ('join' => 'LEFT JOIN '.$u_tabla.' ON unidad='.$u_tabla.'.id');
		$seguimiento_model->_a['views']['por_mm'] = array ('join' => 'LEFT JOIN '.$t_tabla.' ON '.$s_tabla.'.tema='.$t_tabla.'.id LEFT JOIN '.$u_tabla.' ON unidad='.$u_tabla.'.id');
		
		foreach ($materias as $m) {
			$sql_mm = new Gatuf_SQL ('materia=%s AND maestro=%s', array ($m->clave, $maestro->codigo));
			$filter_mm = $sql_mm->gen ();
			$t = array ();
			
			$unidades = $unidad_model->getList (array ('filter' => $filter_mm, 'count' => true));
			$t['unidades'] = $unidades;
			
			$temas = $tema_model->getList (array ('filter' => $filter_mm, 'count' => true, 'view' => 'por_mm'));
			$t['temas'] = $temas;
			
			$seguimientos = $seguimiento_model->getList (array ('filter' => $filter_mm, 'count' => true, 'view' => 'por_mm'));
			$t['seguimientos'] = $seguimientos;
			
			$ultimo_tema = $tema_model->getList (array ('filter' => $filter_mm, 'nb' => 1, 'order' => 'fin DESC', 'view' => 'por_mm'));
			
			if (count ($ultimo_tema) != 0) {
				$t['last_date'] = $ultimo_tema[0]->fin;
			} else {
				$t['last_date'] = null;
			}
			
			$t['seg_sec'] = array ();
			foreach ($secciones[$m->clave] as $s) {
				$sql = new Gatuf_SQL ('nrc=%s', $s->nrc);
				
				$count = $seguimiento_model->getList (array ('filter' => $sql->gen (), 'count' => true));
				$t['seg_sec'][$s->nrc] = $count;
			}
			
			$totals[$m->clave] = $t;
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/reportes/por_maestro.html',
		                                          array ('page_title' => 'Planeación Reportes',
		                                                 'maestro' => $maestro,
		                                                 'materias' => $materias,
		                                                 'secciones' => $secciones,
		                                                 'totales' => $totals
		                                                 ),
		                                          $request);
	}
	
	public $reportePorMateria_precond = array (array ('Gatuf_Precondition::hasPerm', 'Patricia.reportes_planeacion'));
	public function reportePorMateria ($request, $match) {
		$materia = new Pato_Materia ();
		
		if (false === ($materia->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		
		}
		$secciones_list = $materia->get_pato_seccion_list (array ('view' => 'profs_cant'));
		
		$maestros = array ();
		foreach ($secciones_list as $s) {
			$m = $s->get_maestro ();
			$m->cant_grupos = $s->cant_grupos;
			
			$maestros[] = $m;
		}
		
		$totals = array ();
		$tema_model = new Pato_Planeacion_Tema ();
		$unidad_model = new Pato_Planeacion_Unidad ();
		$seguimiento_model = new Pato_Planeacion_Seguimiento ();
		
		$u_tabla = $unidad_model->getSqlTable ();
		$t_tabla = $tema_model->getSqlTable ();
		$s_tabla = $seguimiento_model->getSqlTable ();
		
		$tema_model->_a['views']['por_mm'] = array ('join' => 'LEFT JOIN '.$u_tabla.' ON unidad='.$u_tabla.'.id');
		$seguimiento_model->_a['views']['por_mm'] = array ('join' => 'LEFT JOIN '.$t_tabla.' ON '.$s_tabla.'.tema='.$t_tabla.'.id LEFT JOIN '.$u_tabla.' ON unidad='.$u_tabla.'.id');
		
		foreach ($maestros as $m) {
			$sql_mm = new Gatuf_SQL ('materia=%s AND maestro=%s', array ($materia->clave, $m->codigo));
			$filter_mm = $sql_mm->gen ();
			$t = array ();
			
			$unidades = $unidad_model->getList (array ('filter' => $filter_mm, 'count' => true));
			$t['unidades'] = $unidades;
			
			$temas = $tema_model->getList (array ('filter' => $filter_mm, 'count' => true, 'view' => 'por_mm'));
			$t['temas'] = $temas;
			
			$seguimientos = $seguimiento_model->getList (array ('filter' => $filter_mm, 'count' => true, 'view' => 'por_mm'));
			$t['seguimientos'] = $seguimientos;
			
			$ultimo_tema = $tema_model->getList (array ('filter' => $filter_mm, 'nb' => 1, 'order' => 'fin DESC', 'view' => 'por_mm'));
			
			if (count ($ultimo_tema) != 0) {
				$t['last_date'] = $ultimo_tema[0]->fin;
			} else {
				$t['last_date'] = null;
			}
			
			$totals[$m->codigo] = $t;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/reportes/por_materia.html',
		                                          array ('page_title' => 'Planeación Reportes',
		                                                 'maestros' => $maestros,
		                                                 'materia' => $materia,
		                                                 'totales' => $totals
		                                                 ),
		                                          $request);
	}
}

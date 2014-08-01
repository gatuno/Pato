<?php

class Pato_Materia extends Gatuf_Model {
	/* Manejador de la tabla de materias */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'materias';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 64,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'creditos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
			'curso' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'taller' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'laboratorio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'seminario' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			/*'departamento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => true,
			       'model' => 'Pato_Departamento',
			),*/
			'carreras' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => false,
			       'model' => 'Pato_Carrera',
			       'relate_name' => 'materias',
			),
			'cuatrimestre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
		);
		
		$this->default_order = 'clave ASC, descripcion ASC';
	}
	
	public function getNotEvals ($grupo = null, $count = false) {
		$porcentajes = $this->get_porcentajes_list ();

		$ids = array ();

		foreach ($porcentajes as $p) {
			$ids[] = $p->evaluacion;
		}

		if (count ($ids) == 0) {
			if (!is_null ($grupo)) {
				$where = 'grupo='.$grupo;
			} else {
				$where = '';
			}
			return Gatuf::factory ('Pato_Evaluacion')->getList (array ('count' => $count, 'filter' => $where));
		}

		$where = 'id NOT IN ('.join(', ', $ids).')';

		if (!is_null ($grupo)) {
			$where .= ' AND grupo='.$grupo;
		}

		return Gatuf::factory ('Pato_Evaluacion')->getList (array ('filter' => $where, 'count' => $count));
	}
	
	public function displaylinkedclave ($extra) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($this->clave)).'">'.$this->clave.'</a>';
	}
	
	public function displaytipo ($extra) {
		if ($this->taller && $this->curso) {
			return 'Curso-Taller';
		} else if ($this->curso) {
			return 'Curso';
		} else if ($this->taller) {
			return 'Taller';
		} else if ($this->laboratorio) {
			return 'Laboratorio';
		} else if ($this->seminario) {
			return 'Seminario';
		}
		return 'Desconocido';
	}
	
	function __toString () {
		return $this->descripcion.' ('.$this->clave.')';
	}
}

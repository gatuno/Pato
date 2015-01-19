<?php

class Pato_Seccion extends Gatuf_Model {
	/* Manejador de la tabla de secciones */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'secciones';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'nrc';
		
		$this->_a['cols'] = array (
			'nrc' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'materia' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Materia',
			),
			'seccion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 15,
			),
			'maestro' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Maestro',
			       'relate_name' => 'primario',
			),
			'suplente' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Maestro',
			       'is_null' => true,
			       'default' => null,
			       'relate_name' => 'suplente',
			),
			'asignacion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Carrera',
			       'is_null' => true,
			       'default' => null,
			),
			'cupo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 0,
			),
			'alumnos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => false,
			       'model' => 'Pato_Alumno',
			       'relate_name' => 'grupos',
			),
		);
		
		$this->_a['idx'] = array (
			'mat_sec_idx' =>
			array (
			       'col' => 'materia, seccion',
			       'type' => 'unique',
			),
		);
		
		$this->default_order = 'materia ASC, seccion ASC';
		
		Gatuf::loadFunction ('Pato_Calendario_getDefault');
		$this->_con = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
		
		$this->_a['views'] = array (
			'paginador' => array (
				'select' => $this->_con->pfx.'secciones_view.*',
				'from' => $this->_con->dbname.'.'.$this->_con->pfx.'secciones_view',
				'props' => array ('materia_desc', 'maestro_nombre', 'maestro_apellido'),
			),
		);
	}
	
	function setCalpfx ($calpfx) {
		$this->_con = Pato_Calendario_getDBForCal ($calpfx);
		
		$this->_a['views'] = array (
			'paginador' => array (
				'select' => $this->_con->pfx.'secciones_view.*',
				'from' => $this->_con->dbname.'.'.$this->_con->pfx.'secciones_view',
				'props' => array ('materia_desc', 'maestro_nombre', 'maestro_apellido'),
			),
		);
	}
	
	function preSave ($create = false) {
		if ($create) {
			/* Generar el NRC */
			$max = $this->maxNrc ();
			
			$this->nrc = $max + 1;
		}
	}
	
	function updateAsignacion () {
		$req = sprintf ('UPDATE %s SET asignacion = %s WHERE nrc=%s AND asignacion IS NULL', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($this->asignacion, $this->_con), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute($req);
		
		$req = sprintf ('SELECT asignacion FROM %s WHERE nrc = %s', $this->getSqlTable (), $this->nrc);
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if ($rs[0]['asignacion'] != $this->asignacion) {
			return false;
		}
		return true;
	}
	
	 function liberarAsignacion () {
	 	$this->asignacion = null;
		$req = sprintf ('UPDATE %s SET asignacion = NULL WHERE nrc=%s', $this->getSqlTable (), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con));
		
		$this->_con->execute($req);
		
		return true;
	}
	
	private function maxNrc () {
		$req = sprintf ('SELECT MAX(nrc) AS max_nrc FROM %s', $this->getSqlTable ());
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		if ($rs[0]['max_nrc'] === null) {
			return 0;
		}
		return $rs[0]['max_nrc'];
	}
	
	function maxSeccion ($materia, $tipo = '') {
		if ($tipo != '') {
			$tipo .= '%';
			$req = sprintf ('SELECT MAX(seccion) AS max_seccion FROM %s WHERE materia=%s AND seccion LIKE %s', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($materia, $this->_con), Gatuf_DB_IdentityToDb ($tipo, $this->_con));
		} else {
			$req = sprintf ('SELECT MAX(seccion) AS max_seccion FROM %s WHERE materia=%s', $this->getSqlTable (), Gatuf_DB_IdentityToDb ($materia, $this->_con));
		}
		
		if (false === ($rs = $this->_con->select($req))) {
			throw new Exception($this->_con->getError());
		}
		
		return $rs[0]['max_seccion'];
	}
	
	public function displaylinkedseccion ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($this->nrc)).'">'.$this->seccion.'</a>';
	}
	
	public function displaylinkednrc ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($this->nrc)).'">'.$this->nrc.'</a>';
	}
	
	public function displaylinkedmateria ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Materia::verMateria', array ($this->materia)).'">'.$this->materia.' - '.$this->materia_desc.'</a>';
	}
	
	public function displaylinkedmaestro ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($this->maestro)).'">'.$this->maestro_apellido.' '.$this->maestro_nombre.' ('.$this->maestro.')</a>';
	}
	
	public function displaylinkedmaestro_apellido ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($this->maestro)).'">'.$this->maestro_apellido.' '.$this->maestro_nombre.' ('.$this->maestro.')</a>';
	}
	
	public function displayasignacion ($extra = null) {
		if ($this->asignacion == null) return "Ninguno";
		return $this->asignacion;
	}
}

<?php

class Pato_Calendario extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'calendarios';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'clave';
		
		$this->_a['cols'] = array (
			'clave' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 6,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 20,
			),
			'oculto' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'blank' => false,
			       'default' => false,
			),
			'anio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => false,
			),
			'anio_for_show' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => false,
			),
			'letra' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			),
			'inicio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
			'fin' =>
			array (
			       'type' => 'Gatuf_DB_Field_Date',
			       'blank' => false,
			),
		);
		
		$this->default_order = 'clave DESC';
	}
	
	public function postSave ($create = true) {
		if ($create) {
			/* Crear todos las tablas que cambian entre semestre y semestre */
			$models = array ('Pato_Agenda',
			                 'Pato_Asistencia',
			                 'Pato_Boleta',
			                 'Pato_Horario',
			                 'Pato_Porcentaje',
			                 'Pato_Seccion',
			                 'Pato_Evaluacion_Respuesta',
			                 'Pato_Solicitud_Suficiencia',
			                 'Pato_Planeacion_Seguimiento',
			                 'Pato_Planeacion_Tema',
			                 'Pato_Planeacion_Unidad'
			                 );
			
			$GLOBALS['CAL_ACTIVO'] = $this->clave;
			
			$db = Pato_Calendario_getDBForCal ($this->clave);
			$db->createDB ($db->dbname);
			
			$schema = new Gatuf_DB_Schema ($db);
			foreach ($models as $model) {
				$schema->model = new $model ();
				$schema->createTables ();
			}
	
			foreach ($models as $model) {
				$schema->model = new $model ();
				$schema->createConstraints ();
			}
			
			Pato_Calendario_installVistasSetup ();
			
			/* Disparar la señal, "calendario agregado" */
			$params = array ('db' => $db, 'schema' => $schema, 'calendario' => $this);
			Gatuf_Signal::send ('Pato_Calendario::created', 'Pato_Calendario', $params);
		}
	}
	
	function __toString () {
		return $this->descripcion.' ('.$this->clave.')';
	}
}

function Pato_Calendario_getDBForCal ($calendario) {
	$cal = 'DB_'.$calendario;
	if (isset ($GLOBALS[$cal])) {
		return $GLOBALS[$cal];
	}
	
	$local_db = clone (Gatuf::db ());
	$local_db->dbname = $local_db->dbname.'_'.$calendario;
	
	$GLOBALS[$cal] = $local_db;
	
	return $local_db;
}

function Pato_Calendario_getDefault () {
	return isset ($GLOBALS['CAL_ACTIVO']) ? $GLOBALS['CAL_ACTIVO'] : '';
}

function Pato_Calendario_installVistasSetup ($params = null) {
	/* Crear todas las vistas necesarias */
	$db = Pato_Calendario_getDBForCal (Pato_Calendario_getDefault ());
	$dbpfx = $db->pfx;
	
	$dbname = $db->dbname;
	
	$seccion_tabla = Gatuf::factory ('Pato_Seccion')->getSqlTable ();
	//$horario_tabla = Gatuf::factory ('Pato_Horario')->getSqlTable ();
	
	$materia_tabla = Gatuf::factory ('Pato_Materia')->getSqlTable ();
	$maestro_tabla = Gatuf::factory ('Pato_Maestro')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$dbname.'.'.$dbpfx.'secciones_view AS '."\n"
	    .'SELECT '.$seccion_tabla.'.*, '.$materia_tabla.'.descripcion as materia_desc, '.$maestro_tabla.'.nombre as maestro_nombre, '.$maestro_tabla.'.apellido as maestro_apellido'."\n"
	    .'FROM '.$seccion_tabla."\n"
	    .'LEFT JOIN '.$materia_tabla.' ON '.$seccion_tabla.'.materia = '.$materia_tabla.'.clave'."\n"
	    .'LEFT JOIN '.$maestro_tabla.' ON '.$seccion_tabla.'.maestro = '.$maestro_tabla.'.codigo';
	$db->execute ($sql);
	
	/* Vista de alumnos en este calendario */
	$inscripcion_tabla = Gatuf::factory ('Pato_Inscripcion')->getSqlTable ();
	$alumnos_tabla = Gatuf::factory ('Pato_Alumno')->getSqlTable ();
	$clave = Pato_Calendario_getDefault ();
	
	$sql = 'CREATE VIEW '.$dbname.'.'.$dbpfx.'alumnos_actuales AS '."\n"
	     .'SELECT A.codigo AS alumno, I.carrera AS carrera'."\n"
	     .'FROM '.$alumnos_tabla.' AS A'."\n"
	     .'INNER JOIN '.$inscripcion_tabla.' AS I ON'."\n"
	     .'A.codigo = I.alumno AND STRCMP(I.ingreso, "'.$clave.'") <= 0 AND (I.egreso IS NULL OR STRCMP(I.egreso, "'.$clave.'") >= 0)';
	$db->execute ($sql);
	/* Vista de horarios
	$salon_tabla = Gatuf::factory ('Pato_Salon')->getSqlTable ();
	$carrera_tabla = Gatuf::factory ('Pato_Carrera')->getSqlTable ();
	
	$sql = 'CREATE VIEW '.$dbname.'.'.$dbpfx.'horarios_view AS '."\n"
	     .'SELECT '.$horario_tabla.'.*, '.$salon_tabla.'.aula AS salon_aula, '.$salon_tabla.'.edificio AS salon_edificio,'."\n"
	     .$seccion_tabla.'.maestro AS seccion_maestro, '.$seccion_tabla.'.asignacion AS seccion_asignacion, '.$carrera_tabla.'.color as seccion_asignacion_color'."\n"
	     .'FROM '.$horario_tabla."\n"
	     .'LEFT JOIN '.$salon_tabla.' ON '.$horario_tabla.'.salon = '.$salon_tabla.'.id'."\n"
	     .'LEFT JOIN '.$seccion_tabla.' ON '.$horario_tabla.'.nrc = '.$seccion_tabla.'.nrc'."\n"
	     .'LEFT JOIN '.$carrera_tabla.' ON '.$seccion_tabla.'.asignacion = '.$carrera_tabla.'.clave';
	$db->execute ($sql);*/
}


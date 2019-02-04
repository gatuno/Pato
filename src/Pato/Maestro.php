<?php

class Pato_Maestro extends Gatuf_Model {
	/* Manejador de la tabla Maestros */
	public $_model = __CLASS__;
	public $session_key = '_GATUF_Gatuf_User_auth';
	
	public $_cache_perms = null;
	
	function init () {
		$this->_a['table'] = 'maestros';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 70,
			),
			'apellido' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 140,
			),
			'sexo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'M',
			       'size' => 1,
			),
			'grado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'L',
			       'size' => 1,
			),
			'tiempo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => null, // Tiempo Completo, Medio tiempo
			       'is_null' => true,
			),
			'email' =>
			array (
			       'type' => 'Gatuf_DB_Field_Email',
			       'blank' => false,
			),
			'password' =>
			array (
			       'type' => 'Gatuf_DB_Field_Password',
			       'blank' => false,
			       'size' => 150,
			),
			'groups' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => true,
			       'model' => Gatuf::config ('gatuf_custom_group', 'Gatuf_Group'),
			       'relate_name' => 'maestros',
			),
			'permissions' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => true,
			       'model' => 'Gatuf_Permission',
			),
			'administrator' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'default' => false,
			       'blank' => true,
			),
			'active' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'default' => true,
			       'blank' => true,
			),
			'last_login' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'editable' => false,
			       'is_null' => true,
			       'default' => null,
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	function postSave ($create = false) {
		
	}
	
	function setPassword ($password) {
		$salt = Gatuf_Utils::getRandomString(5);
		$this->password = 'sha1:'.$salt.':'.sha1($salt.$password);
		return true;
	}
	
	function checkPassword ($password) {
		if ($this->password == '') {
			return false;
		}
		list ($algo, $salt, $hash) = explode(':', $this->password);
		if ($hash == $algo($salt.$password)) {
			return true;
		} else {
			return false;
		}
	}
	
	function isAnonymous () {
		return (0 === (int)$this->codigo);
	}
	
	function getAllPermissions ($force=false) {
		if ($force == false and !is_null($this->_cache_perms)) {
			return $this->_cache_perms;
		}
		
		$this->_cache_perms = array ();
		$perms = (array) $this->get_permissions_list ();
		$groups = $this->get_groups_list ();
		$ids = array ();
		foreach ($groups as $group) {
			$ids[] = $group->id;
		}
		
		if (count ($ids) > 0) {
			$gperm = new Gatuf_Permission ();
			$f_name = strtolower (Gatuf::config ('gatuf_custom_group', 'Gatuf_Group')).'_id';
			$perms = array_merge ($perms, (array) $gperm->getList (array ('filter' => $f_name.' IN ('.join(', ', $ids).')', 'view' => 'join_group')));
		}
		foreach ($perms as $perm) {
			if (!in_array ($perm->application.'.'.$perm->code_name, $this->_cache_perms)) {
				$this->_cache_perms[] = $perm->application.'.'.$perm->code_name;
			}
		}
		return $this->_cache_perms;
	}
	
	function hasPerm ($perm, $obj = null) {
		if ($this->isAnonymous ()) return false;
		if (!$this->active) return false;
		if ($this->administrator) return true;
		$perms = $this->getAllPermissions ();
		
		if (in_array ($perm, $perms)) return true;
		
		return false;
	}
	
	function hasAppPerms ($app) {
		if ($this->administrator) return true;
		
		foreach ($this->getAllPermissions() as $perm) {
			if (0 === strpos ($perm, $app.'.')) return true;
		}
		return false;
	}
	
	function setMessage ($type, $message) {
		if ($this->isAnonymous ()) {
			return false;
		}
		
		$m = new Gatuf_Message ();
		$m->message = $message;
		$m->type = $type;
		$m->user = $this;
		
		return $m->create ();
	}
	
	function getAndDeleteMessages () {
		if ($this->isAnonymous ()) {
			return false;
		}
		$messages = new ArrayObject ();
		$ms = $this->get_gatuf_message_list();
		foreach ($ms as $m) {
			$messages[] = array ('message' => $m->message, 'type' => $m->type);
			$m->delete ();
		}
		
		return $messages;
	}
	
	/*
	 * TODO: Revisar esto
	function isCoord () {
		if (!$this->active) return false;
		if ($this->administrator) return true;
		
		$perms = $this->getAllPermissions ();
		
		$coords = preg_grep ('/Patricia.coordinador.*-/', $perms);
		
		if (count ($coords) > 0) return true;
		return false;
	}
	
	function returnCoord () {
		if (!$this->active) return false;
		
		$perms = $this->getAllPermissions ();
		
		$coords = preg_grep ('/Patricia.coordinador.*-/', $perms);
		
		return $coords;
	}*/
	
	function get_type () {
		return 'm';
	}
	
	function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	function __toString () {
		return $this->apellido.' '.$this->nombre.' ('.$this->codigo.')';
	}
	
	function displaygrado ($extra = null) {
		switch ($this->grado) {
			case 'I':
				return 'Ing.';
				break;
			case 'L':
				return 'Lic.';
				break;
			case 'D':
				if ($this->sexo == 'F') return 'Dra.';
				else return 'Dr.';
				break;
			case 'M':
				if ($this->sexo == 'F') return 'Mtra.';
				else return 'Mtro.';
				break;
			default:
				throw new Exception ('No implementado');
		}
	}
}

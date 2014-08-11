<?php
class Pato_Template_Tag_MateriaCoord extends Gatuf_Template_Tag {
	/**
	 * @param string Variable to get the permission
	 * @param Pluf_User
	 * @param string Permission string
	 * @param mixed Optional Pluf_Model if using row level permission (null)
	 */
	function start($var, $user, $materia) {
		$cars = $materia->get_carreras_list ();
		$found = $user->administrator;
		foreach ($cars as $car) {
			if ($user->hasPerm ('Patricia.coordinador.'.$car->clave)) {
				$found = true;
				break;
			}
		}
		
		$this->context->set($var, $found);
	}
}

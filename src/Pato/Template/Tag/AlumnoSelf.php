<?php

class Pato_Template_Tag_AlumnoSelf extends Gatuf_Template_Tag {
	function start ($var, $user, $alumno) {
		if (get_class ($user) == 'Pato_Alumno' && $user->codigo == $alumno->codigo) {
			$this->context->set($var, true);
		} else {
			$this->context->set($var, false);
		}
	}
}

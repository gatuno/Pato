<?php

class Pato_Template_Tag_MaestroSelf extends Gatuf_Template_Tag {
	function start ($var, $user, $maestro) {
		if (get_class ($user) == 'Pato_Maestro' && $user->codigo == $maestro->codigo) {
			$this->context->set($var, true);
		} else {
			$this->context->set($var, false);
		}
	}
}

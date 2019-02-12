<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Reportes {
	public $index_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.reportes_todos')));
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/reportes/index.html',
		                                         array('page_title' => 'Reportes varios'),
                                                 $request);
	}
}

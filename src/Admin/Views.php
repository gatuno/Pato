<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Admin_Views {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('admin/index.html',
		                                         array ('page_title' => 'Administración'),
		                                         $request);
	}
}

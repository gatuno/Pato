<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Admision_Views {
	public function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('admision/index.html',
		                                         array('page_title' => 'Admision'),
                                                 $request);
	}
}

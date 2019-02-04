<?php

class Pato_EmptyUser {
	/* Modelo falso para llenar el usuario */
	public $_model = __CLASS__;
	public $administrator = false;
	public $session_key = '_GATUF_Gatuf_User_auth';
	
	function isAnonymous () {
		return true;
	}
	
	function getAllPermissions ($force=false) {
		return array ();
	}
	
	function hasPerm ($perm, $obj = null) {
		return false;
	}
	
	function hasAppPerms ($app) {
		return false;
	}
	
	function setMessage ($type, $message) {
		return false;
	}
	
	function getAndDeleteMessages () {
		return false;
	}
}	

<?php
/* -*- tab-width: 4; indent-tabs-mode: nil; c-basic-offset: 4 -*- */
/*
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Plume Framework, a simple PHP Application Framework.
# Copyright (C) 2001-2007 Loic d'Anterroches and contributors.
#
# Plume Framework is free software; you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as published by
# the Free Software Foundation; either version 2.1 of the License, or
# (at your option) any later version.
#
# Plume Framework is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
#
# ***** END LICENSE BLOCK ***** */

/**
 * Backend to authenticate against the Pluf_User model.
 */
class Pato_Auth_MaestroBackend {
    /**
     * Given a user id, retrieve it.
     *
     * In the case of the Pluf_User backend, the $user_id is the login.
     */
    public static function getUser($codigo) {
        /* Recuperar el usuario */
        $sql = new Gatuf_SQL ('codigo=%s', array ($codigo));
        return Gatuf::factory ('Pato_Maestro')->getOne ($sql->gen());
    }

    /**
     * Given an array with the authentication data, auth the user and return it.
     */
    public static function authenticate($auth_data) {
        if (!isset ($auth_data['password']) || !isset ($auth_data['login'])) {
            return false;
        }
        $password = $auth_data['password'];
        $login = $auth_data['login'];
        $user = self::getUser($login);
        if (!$user) {
            return false;
        }
        if (!$user->active) {
            return false;
        }
        return ($user->checkPassword($password)) ? $user : false;
    }
}


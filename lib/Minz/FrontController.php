<?php
# ***** BEGIN LICENSE BLOCK *****
# MINZ - a free PHP Framework like Zend Framework
# Copyright (C) 2011 Marien Fressinaud
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

/**
 * La classe FrontController est le Dispatcher du framework, elle lance l'application
 * Elle est appelée en général dans le fichier index.php à la racine du serveur
 */
class Minz_FrontController {
	protected $dispatcher;
	protected $router;

	private $useOb = true;

	/**
	 * Constructeur
	 * Initialise le router et le dispatcher
	 */
	public function __construct () {
		if (LOG_PATH === false) {
			$this->killApp ('Path not found: LOG_PATH');
		}

		try {
			Minz_Configuration::init ();

			Minz_Request::init ();

			$this->router = new Minz_Router ();
			$this->router->init ();
		} catch (Minz_RouteNotFoundException $e) {
			Minz_Log::record ($e->getMessage (), Minz_Log::ERROR);
			Minz_Error::error (
				404,
				array ('error' => array ($e->getMessage ()))
			);
		} catch (Minz_Exception $e) {
			Minz_Log::record ($e->getMessage (), Minz_Log::ERROR);
			$this->killApp ($e->getMessage ());
		}

		$this->dispatcher = Minz_Dispatcher::getInstance ($this->router);
	}

	/**
	 * Démarre l'application (lance le dispatcher et renvoie la réponse
	 */
	public function run () {
		try {
			$this->dispatcher->run ($this->useOb);
			Minz_Response::send ();
		} catch (Minz_Exception $e) {
			try {
				Minz_Log::record ($e->getMessage (), Minz_Log::ERROR);
			} catch (Minz_PermissionDeniedException $e) {
				$this->killApp ($e->getMessage ());
			}

			if ($e instanceof Minz_FileNotExistException ||
					$e instanceof Minz_ControllerNotExistException ||
					$e instanceof Minz_ControllerNotActionControllerException ||
					$e instanceof Minz_ActionException) {
				Minz_Error::error (
					404,
					array ('error' => array ($e->getMessage ())),
					true
				);
			} else {
				$this->killApp ();
			}
		}
	}

	/**
	* Permet d'arrêter le programme en urgence
	*/
	private function killApp ($txt = '') {
		if ($txt == '') {
			$txt = 'See logs files';
		}
		exit ('### Application problem ###<br />'."\n".$txt);
	}

	public function useOb() {
		return $this->useOb;
	}

	/**
	 * Use ob_start('ob_gzhandler') or not.
	 */
	public function _useOb($ob) {
		return $this->useOb = (bool)$ob;
	}
}
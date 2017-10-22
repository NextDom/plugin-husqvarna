<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../3rdparty/husqvarna_api.class.php';

class husqvarna extends eqLogic {
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */
	public static function postConfig_password() {
		husqvarna::force_detect_movers();
	}

	public static function force_detect_movers() {
		// Initialisation de la connexion
		log::add('husqvarna','info','force_detect_movers');
		if ( config::byKey('account', 'husqvarna') != "" || config::byKey('password', 'husqvarna') != "" )
		{
			$session_husqvarna = new husqvarna_api();
			$session_husqvarna->login(config::byKey('account', 'husqvarna'), config::byKey('password', 'husqvarna'));
			foreach ($session_husqvarna->list_robots() as $id => $data)
			{
				log::add('husqvarna','debug','Find mover : '.$id);
				if ( ! is_object(self::byLogicalId($id, 'husqvarna')) ) {
					log::add('husqvarna','info','Creation husqvarna : '.$id.' ('.$data->name.')');
					$eqLogic = new husqvarna();
					$eqLogic->setLogicalId($id);
					$eqLogic->setName($data->name);
					$eqLogic->setEqType_name('husqvarna');
					$eqLogic->setIsEnable(1);
					$eqLogic->save();
				}
			}
		}
	}

	public function postInsert()
	{
        $state = $this->getCmd(null, 'state');
        if ( ! is_object($state) ) {
            $state = new husqvarnaCmd();
			$state->setName('Etat');
			$state->setEqLogic_id($this->getId());
			$state->setType('info');
			$state->setSubType('binary');
			$state->setLogicalId('state');
			$state->setDisplay('generic_type','PRESENCE');
			$state->setDisplay('invertBinary',1);
			$state->setTemplate('dashboard', 'presence');
			$state->setTemplate('mobile', 'presence');
			$state->save();
		}
/*         $lastfilename = $this->getCmd(null, 'lastfilename');
        if ( ! is_object($lastfilename) ) {
            $lastfilename = new husqvarnaCmd();
			$lastfilename->setName('Nom du dernier fichier');
			$lastfilename->setEqLogic_id($this->getId());
			$lastfilename->setType('info');
			$lastfilename->setSubType('string');
			$lastfilename->setLogicalId('lastfilename');
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
		} */
	}

	public function postUpdate()
	{
        $state = $this->getCmd(null, 'state');
        if ( ! is_object($state) ) {
            $state = new husqvarnaCmd();
			$state->setName('Etat');
			$state->setEqLogic_id($this->getId());
			$state->setType('info');
			$state->setSubType('binary');
			$state->setLogicalId('state');
			$state->setDisplay('invertBinary',1);
			$state->setDisplay('generic_type','PRESENCE');
			$state->setTemplate('dashboard', 'presence');
			$state->setTemplate('mobile', 'presence');
			$state->save();
			$restart = true;
		}
/*         $lastfilename = $this->getCmd(null, 'lastfilename');
        if ( ! is_object($lastfilename) ) {
            $lastfilename = new husqvarnaCmd();
			$lastfilename->setName('Nom du dernier fichier');
			$lastfilename->setEqLogic_id($this->getId());
			$lastfilename->setType('info');
			$lastfilename->setSubType('string');
			$lastfilename->setLogicalId('lastfilename');
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
			$restart = true;
		}
		else
		{
			$lastfilename->setTemplate('dashboard', 'lastfilename');
			$lastfilename->setTemplate('mobile', 'lastfilename');
			$lastfilename->save();
		}
 */
	}

	public function preRemove() {
	}

	public static function pull() {
		foreach (self::byType('husqvarna') as $eqLogic) {
			$eqLogic->scan();
		}
	}

	public function scan() {
		if ( config::byKey('account', 'husqvarna') != "" || config::byKey('password', 'husqvarna') != "" )
		{
			$session_husqvarna = new husqvarna_api();
			$session_husqvarna->login(config::byKey('account', 'husqvarna'), config::byKey('password', 'husqvarna'));
			if ( $this->getIsEnable() ) {
				if ( $this->getCookiesInfo() ) {
					$this->refreshInfo();
					$this->logOut();
				}
			}
		}
	}
}

class husqvarnaCmd extends cmd 
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
	public function postInsert()
	{
		if ( ! defined($this->logicalId) || $this->logicalId == "" )
			$this->logicalId = 'pattern';
	}
}
?>

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
                    $eqLogic->setIsVisible(1);
                    $eqLogic->save();
                }
            }
        }
    }

    public function postInsert()
    {
        $this->postUpdate();
    }

    private function getListeDefaultCommandes()
    {
        return array(    "batteryPercent" => array('Batterie', 'info', 'numeric', "%", 0, "GENERIC_INFO", 'badge', 'badge', ''),
                        "connected" => array('Connecté', 'info', 'binary', "", 0, "GENERIC_INFO", 'alert', 'alert', ''),
                        "mowerStatus" => array('Etat robot', 'info', 'string', "", 0, "GENERIC_INFO", 'badge', 'badge', ''),
                        "operatingMode" => array('Mode de fonctionnement', 'info', 'string', "", 0, "GENERIC_INFO", 'badge', 'badge', ''),
                        "lastErrorCode" => array('Dernier code d\'erreur', 'info', 'numeric', "", 0, "GENERIC_INFO", 'badge', 'badge', ''),
                        "commande" => array('Commande', 'action', 'select', "", 0, "GENERIC_ACTION", '', '', 'START|'.__('Démarrer',__FILE__).';STOP|'.__('Arrêter',__FILE__).';PARK|'.__('Ranger',__FILE__)),
                        "nextStartSource" => array('Prochain départ', 'info', 'string', "", 0, "GENERIC_INFO", 'badge', 'badge', ''),
                        "nextStartTimestamp" => array('Heure prochain départ', 'info', 'string', "u", 0, "GENERIC_INFO", 'badge', 'badge', ''),
                        "storedTimestamp" => array('Heure dernier rapport', 'info', 'string', "u", 0, "GENERIC_INFO", 'badge', 'badge', '')
        );
    }

    public function postUpdate()
    {
        foreach( $this->getListeDefaultCommandes() as $id => $data)
        {
            list($name, $type, $subtype, $unit, $invertBinary, $generic_type, $template_dashboard, $template_mobile, $listValue) = $data;
            $cmd = $this->getCmd(null, $id);
            if ( ! is_object($cmd) ) {
                $cmd = new husqvarnaCmd();
                $cmd->setName($name);
                $cmd->setEqLogic_id($this->getId());
                $cmd->setType($type);
                $cmd->setSubType($subtype);
                $cmd->setLogicalId($id);
                if ( $listValue != "" )
                {
                    $cmd->setConfiguration('listValue', $listValue);
                }
                $cmd->setDisplay('invertBinary',$invertBinary);
                $cmd->setDisplay('generic_type', $generic_type);
                $cmd->setTemplate('dashboard', $template_dashboard);
                $cmd->setTemplate('mobile', $template_mobile);
                $cmd->save();
            }
            else
            {
                if ( $cmd->getType() == "" )
                {
                    $cmd->setType($type);
                }
                if ( $cmd->getSubType() == "" )
                {
                    $cmd->setSubType($subtype);
                }
                if ( $cmd->getDisplay('invertBinary') == "" )
                {
                    $cmd->setDisplay('invertBinary',$invertBinary);
                }
                if ( $cmd->getDisplay('generic_type') == "" )
                {
                    $cmd->setDisplay('generic_type', $generic_type);
                }
                if ( $cmd->getDisplay('dashboard') == "" )
                {
                    $cmd->setTemplate('dashboard', $template_dashboard);
                }
                if ( $cmd->getDisplay('mobile') == "" )
                {
                    $cmd->setTemplate('mobile', $template_mobile);
                }
                if ( $listValue != "" )
                {
                    $cmd->setConfiguration('listValue', $listValue);
                }
                $cmd->save();
            }
        }
    }

    public function preRemove() {
    }

    public static function pull() {
        if ( config::byKey('account', 'husqvarna') != "" || config::byKey('password', 'husqvarna') != "" )
        {
            log::add('husqvarna','debug','scan movers info');
            foreach (self::byType('husqvarna') as $eqLogic) {
                $eqLogic->scan();
            }
        }
    }

    public function scan() {
        $session_husqvarna = new husqvarna_api();
        $session_husqvarna->login(config::byKey('account', 'husqvarna'), config::byKey('password', 'husqvarna'));
        if ( $this->getIsEnable() ) {
            $status = $session_husqvarna->get_status($this->getLogicalId());
            log::add('husqvarna','debug',"Refresh Status ".$this->getLogicalId());
            foreach( $this->getListeDefaultCommandes() as $id => $data)
            {
                list($name, $type, $subtype, $unit, $invertBinary, $generic_type, $template_dashboard, $template_mobile, $listValue) = $data;
                if ( $type != "action" )
                {
                    $cmd = $this->getCmd(null, $id);
                    if ($cmd->execCmd() != $cmd->formatValue($status->{$id}))
                    {
                        log::add('husqvarna','info',"Refresh ".$id." : ".$status->{$id});
                        $cmd->setCollectDate('');
                        if ($unit  != "u" )
                        {
                            $cmd->event($status->{$id});
                        }
                        else
                        {
                            if ( $status->{$id} == 0 )
                            {
                                $cmd->event(__('Inconnue',__FILE__));
                            } else {
                                $cmd->event( date('d M Y H:i', intval(substr($status->{$id},0,10)) - 3600 * (date('I')+1) ));
                            }
                        }
                    }
                }
            }
        }
        $session_husqvarna->logOut();
    }
}

class husqvarnaCmd extends cmd 
{
    /*     * *************************Attributs****************************** */
    public function execute($_options = null) {
        if ( $this->getLogicalId() == 'commande' && $_options['select'] != "" )
        {
            log::add('husqvarna','info',"Commande execute ".$this->getLogicalId()." ".$_options['select']);
            $session_husqvarna = new husqvarna_api();
            $session_husqvarna->login(config::byKey('account', 'husqvarna'), config::byKey('password', 'husqvarna'));
            $eqLogic = $this->getEqLogic();

            $order = $session_husqvarna->control($eqLogic->getLogicalId(), $_options['select']);
            log::add('husqvarna','debug',"Commande traité : Code = ".$order->status);
        }
    }


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*     * **********************Getteur Setteur*************************** */
}
?>

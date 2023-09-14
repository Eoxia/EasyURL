<?php
/* Copyright (C) 2023 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modEasyurl_EasyurlTriggers.class.php
 * \ingroup easyurl
 * \brief   EasyURL trigger
 */

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';

// Load EasyURL libraries
require_once __DIR__ . '/../../lib/easyurl_function.lib.php';

/**
 * Class of triggers for EasyURL module
 */
class InterfaceEasyURLTriggers extends DolibarrTriggers
{
    /**
     * @var DoliDB Database handler
     */
    protected $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;

        $this->name        = preg_replace('/^Interface/i', '', get_class($this));
        $this->family      = 'demo';
        $this->description = 'EasyURL triggers';
        $this->version     = '1.0.0';
        $this->picto       = 'easyurl@easyurl';
    }

    /**
     * Trigger name
     *
     * @return string Name of trigger file
     */
    public function getName(): string
    {
        return parent::getName();
    }

    /**
     * Trigger description
     *
     * @return string Description of trigger file
     */
    public function getDesc(): string
    {
        return parent::getDesc();
    }

    /**
     * Function called when a Dolibarr business event is done
     * All functions "runTrigger" are triggered if file
     * is inside directory core/triggers
     *
     * @param  string       $action Event action code
     * @param  CommonObject $object Object
     * @param  User         $user   Object user
     * @param  Translate    $langs  Object langs
     * @param  Conf         $conf   Object conf
     * @return int                  0 < if KO, 0 if no triggered ran, >0 if OK
     * @throws Exception
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf): int
    {
        if (!isModEnabled('easyurl')) {
            return 0; // If module is not enabled, we do nothing
        }

        // Data and type of action are stored into $object and $action
        dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . '. id=' . $object->id);

        switch ($action) {
            case 'PROPAL_VALIDATE':
            case 'CONTRACT_VALIDATE':
            case 'FICHINTER_VALIDATE':
                if (getDolGlobalInt('EASYURL_AUTOMATIC_GENERATION')) {
                    set_easy_url_link($object, 'signature');
                }
                break;
            case 'ORDER_VALIDATE':
            case 'BILL_VALIDATE':
                if (getDolGlobalInt('EASYURL_AUTOMATIC_GENERATION')) {
                    set_easy_url_link($object, 'payment');
                }
                break;
        }
        return 0;
    }
}

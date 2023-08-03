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
 * \file    core/triggers/interface_99_modTinyURL_DolisirhTriggers.class.php
 * \ingroup tinyurl
 * \brief   TinyURL trigger
 */

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';

/**
 * Class of triggers for TinyURL module
 */
class InterfaceTinyURLTriggers extends DolibarrTriggers
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
        $this->description = 'TinyURL triggers.';
        $this->version     = '1.0.0';
        $this->picto       = 'tinyurl@tinyurl';
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
        if (!isModEnabled('tinyurl')) {
            return 0; // If module is not enabled, we do nothing
        }

        // Data and type of action are stored into $object and $action
        dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . '. id=' . $object->id);

        switch ($action) {
            case 'PROPAL_VALIDATE':
            case 'ORDER_VALIDATE':
            case 'BILL_VALIDATE':
                $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
                $checkConf        = getDolGlobalString('TINYURL_URL_YOURLS_API') && getDolGlobalString('TINYURL_SIGNATURE_TOKEN_YOURLS_API');
                if ($useOnlinePayment && $checkConf) {
                    require_once DOL_DOCUMENT_ROOT . '/core/lib/payments.lib.php';
                    require_once DOL_DOCUMENT_ROOT . '/core/lib/ticket.lib.php';

                    $object->fetch($object->id);
                    $onlinePaymentURL = getOnlinePaymentUrl(0, 'invoice', $object->ref);

                    $title = dol_sanitizeFileName($conf->global->MAIN_INFO_SOCIETE_NOM . '-' . strtolower($object->ref) . (getDolGlobalInt('TINYURL_USE_SHA_URL') ? '-' . generate_random_id(8) : ''));

                    // Init the CURL session
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $conf->global->TINYURL_URL_YOURLS_API);
                    curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
                    curl_setopt($ch, CURLOPT_POSTFIELDS, [               // Data to POST
                        'action'    => 'shorturl',
                        'signature' => $conf->global->TINYURL_SIGNATURE_TOKEN_YOURLS_API,
                        'format'    => 'json',
                        'title'     => $title,
                        'keyword'   => $title,
                        'url'       => $onlinePaymentURL
                    ]);

                    // Fetch and return content
                    $data = curl_exec($ch);
                    curl_close($ch);

                    // Do something with the result
                    $data = json_decode($data);
                    $object->array_options['options_tiny_url_link'] = $data->shorturl;
                    $object->update($user, false);
                }

                break;
        }
        return 0;
    }
}

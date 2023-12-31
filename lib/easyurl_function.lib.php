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
* \file    lib/easyurl_function.lib.php
* \ingroup easyurl
* \brief   Library files with common functions for EasyURL
*/

/**
 * Set easy url link
 *
 * @param CommonObject $object  Object
 * @param string       $urlType Url type
 */
function set_easy_url_link(CommonObject $object, string $urlType)
{
    global $conf, $langs;

    $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
    $checkConf        = getDolGlobalString('EASYURL_URL_YOURLS_API') && getDolGlobalString('EASYURL_SIGNATURE_TOKEN_YOURLS_API');
    if ((($urlType == 'payment' && $useOnlinePayment) || $urlType == 'signature') && $checkConf) {
        // Load Dolibarr libraries
        require_once DOL_DOCUMENT_ROOT . '/core/lib/payments.lib.php';
        require_once DOL_DOCUMENT_ROOT . '/core/lib/signature.lib.php';
        require_once DOL_DOCUMENT_ROOT . '/core/lib/ticket.lib.php';

        $object->fetch($object->id);
        switch ($object->element) {
            case 'propal' :
                $type = 'proposal';
                break;
            case 'commande' :
                $type = 'order';
                break;
            case 'facture' :
                $type = 'invoice';
                break;
            case 'contrat' :
                $type = 'contract';
                break;
            default :
                $type = $object->element;
                break;
        }
        switch ($urlType) {
            case 'payment' :
                $onlineUrl = getOnlinePaymentUrl(0, $type, $object->ref);
                break;
            case 'signature' :
                $onlineUrl = getOnlineSignatureUrl(0, $type, $object->ref);
                break;
            default :
                $onlineUrl = '';
                break;
        }

        $title = dol_sanitizeFileName(dol_strtolower($conf->global->MAIN_INFO_SOCIETE_NOM . '-' . $object->ref) . (getDolGlobalInt('EASYURL_USE_SHA_URL') ? '-' . generate_random_id(8) : ''));

        // Init the CURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $conf->global->EASYURL_URL_YOURLS_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, [               // Data to POST
            'action'    => 'shorturl',
            'signature' => $conf->global->EASYURL_SIGNATURE_TOKEN_YOURLS_API,
            'format'    => 'json',
            'title'     => $title,
            'keyword'   => $title,
            'url'       => $onlineUrl
        ]);

        // Fetch and return content
        $data = curl_exec($ch);
        curl_close($ch);

        // Do something with the result
        $data = json_decode($data);
        if ($data->status == 'success') {
            $object->array_options['options_easy_url_' . $urlType . '_link'] = $data->shorturl;
            $object->updateExtraField('easy_url_' . $urlType . '_link');
            setEventMessage($langs->trans('SetEasyURLSuccess'));
        } else {
            setEventMessage($langs->trans('SetEasyURLErrors'), 'errors');
        }
    }
}

/**
 * get easy url link
 *
 * @param  CommonObject $object  Object
 * @param  string       $urlType Url type
 * @return int                   0 < on error, 1 = statusCode 200, 0 = other statusCode (ex : 404)
 */
function get_easy_url_link(CommonObject $object, string $urlType): int
{
    global $conf;

    $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
    $checkConf        = getDolGlobalString('EASYURL_URL_YOURLS_API') && getDolGlobalString('EASYURL_SIGNATURE_TOKEN_YOURLS_API');
    if ((($urlType == 'payment' && $useOnlinePayment) || $urlType == 'signature') && $checkConf) {
        $object->fetch($object->id);

        // Init the CURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $conf->global->EASYURL_URL_YOURLS_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, [               // Data to POST
            'action'    => 'url-stats',
            'signature' => $conf->global->EASYURL_SIGNATURE_TOKEN_YOURLS_API,
            'format'    => 'json',
            'shorturl'  => $object->array_options['options_easy_url_' . $urlType . '_link']
        ]);

        // Fetch and return content
        $data = curl_exec($ch);
        curl_close($ch);

        // Do something with the result
        $data = json_decode($data);
        return $data->statusCode == 200 ? 1 : 0;
    } else {
        return -1;
    }
}

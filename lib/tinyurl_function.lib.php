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
* \file    lib/tinyurl_function.lib.php
* \ingroup tinyurl
* \brief   Library files with common functions for TinyURL
*/

/**
 * Set tiny url link
 *
 * @param CommonObject $object Object
 */
function set_tiny_url_link(CommonObject $object) {
    global $conf, $langs, $user;

    $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
    $checkConf        = getDolGlobalString('TINYURL_URL_YOURLS_API') && getDolGlobalString('TINYURL_SIGNATURE_TOKEN_YOURLS_API');
    if ($useOnlinePayment && $checkConf) {
        // Load Dolibarr libraries
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
        if ($data->status == 'success') {
            setEventMessage($langs->trans('SetTinyURLSuccess'));
        } else {
            setEventMessage($langs->trans('SetTinyURLErrors'), 'errors');
        }
    }
}

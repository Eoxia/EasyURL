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
 * Init easy url curl
 * @param  array        $curlPostFields cURL post fields
 * @param  string       $urlMethod      Url method
 * @return int|stdClass                 0 < on error, data object on success
 */
function init_easy_url_curl(array $curlPostFields, string $urlMethod = 'yourls')
{
    global $langs;

    // Check configuration
    $urlAPI         = getDolGlobalString('EASYURL_URL_' . dol_strtoupper($urlMethod) . '_API');
    $signatureToken = getDolGlobalString('EASYURL_SIGNATURE_TOKEN_' . dol_strtoupper($urlMethod) . '_API');
    $checkConf      = $urlAPI && $signatureToken;
    if (!$checkConf) {
        setEventMessages($langs->trans('ErrorMissingEasyURLAPIConfig'), [], 'errors');
        return -1;
    }

    // Init the CURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlAPI);
    curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
    switch ($urlMethod) {
        case 'yourls' :
            // Data to POST
            $defaultCurlPostFields = [
                'signature' => $signatureToken,
                'format'    => 'json'
            ];
            $curlPostFields = array_merge($curlPostFields, $defaultCurlPostFields);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPostFields);
            break;
        case 'wordpress' :
            break;
    }

    // Fetch and return content
    $data = curl_exec($ch);
    curl_close($ch);

    // Do something with the result
    return json_decode($data);
}

/**
 * Set easy url link
 *
 * @param  Shortener         $shortener Shortener
 * @param  string            $urlType   Url type
 * @param  CommonObject|null $object    Object
 * @param  string            $urlMethod Url method
 * @return int|object        $data      Data error after curl
 */
function set_easy_url_link(Shortener $shortener, string $urlType, CommonObject $object = null, string $urlMethod = 'yourls')
{
    global $conf, $langs, $user;

    $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
    if (($urlType == 'payment' && $useOnlinePayment) || $urlType == 'signature' || $urlType == 'none') {
        // Load Dolibarr libraries
        require_once DOL_DOCUMENT_ROOT . '/core/lib/payments.lib.php';
        require_once DOL_DOCUMENT_ROOT . '/core/lib/signature.lib.php';
        require_once DOL_DOCUMENT_ROOT . '/core/lib/ticket.lib.php';

        $shortener->fetch($shortener->id);
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
                if ($type == 'proposal') {
                    $type = 'propal';
                }
                $shortener->element_type = $type;
                $shortener->fk_element   = $object->id;
                $shortener->status       = $shortener::STATUS_ASSIGN;
                break;
            case 'signature' :
                $onlineUrl = getOnlineSignatureUrl(0, $type, $object->ref);
                if ($type == 'proposal') {
                    $type = 'propal';
                }
                $shortener->element_type = $type;
                $shortener->fk_element   = $object->id;
                $shortener->status       = $shortener::STATUS_ASSIGN;
                break;
            default :
                if (property_exists($shortener, 'original_url') && dol_strlen($shortener->original_url) > 0) {
                    $onlineUrl = $shortener->original_url;
                } else {
                    $onlineUrl = getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL');
                }
                break;
        }

        $title  = getDolGlobalInt('EASYURL_USE_MAIN_INFO_SOCIETE_NAME') ? dol_strtolower($conf->global->MAIN_INFO_SOCIETE_NOM) : '';
        $title .= getDolGlobalInt('EASYURL_USE_MAIN_INFO_SOCIETE_NAME') && getDolGlobalInt('EASYURL_USE_SHORTENER_REF') ? '-' : '';
        $title .= getDolGlobalInt('EASYURL_USE_SHORTENER_REF') ? dol_strtolower($shortener->ref) : '';
        $title .= (getDolGlobalInt('EASYURL_USE_MAIN_INFO_SOCIETE_NAME') || getDolGlobalInt('EASYURL_USE_SHORTENER_REF')) && getDolGlobalInt('EASYURL_USE_SHA_URL') ? '-' : '';
        $title .= getDolGlobalInt('EASYURL_USE_SHA_URL') ? generate_random_id(8) : '';
        $title  = dol_sanitizeFileName($title);

        $curlPostFields = [
            'action'  => 'shorturl',
            'title'   => $title,
            'keyword' => $title,
            'url'     => $onlineUrl
        ];
        $data = init_easy_url_curl($curlPostFields, $urlMethod);

        if ($data != null && $data->status == 'success') {
            $shortenerUrlTypeDictionaries = saturne_fetch_dictionary('c_shortener_url_type');
            if (is_array($shortenerUrlTypeDictionaries) && !empty($shortenerUrlTypeDictionaries)) {
                foreach ($shortenerUrlTypeDictionaries as $shortenerUrlTypeDictionary) {
                    if ($shortenerUrlTypeDictionary->ref == ucfirst($urlType)) {
                        $shortener->type = $shortenerUrlTypeDictionary->id;
                        break;
                    }
                }
            }

            if ($shortener->status == $shortener::STATUS_DRAFT) {
                $shortener->status = $shortener::STATUS_VALIDATED;
            }
            $shortener->label        = $title;
            $shortener->short_url    = $data->shorturl;
            $shortener->original_url = $onlineUrl;
            $shortener->update($user, true);

            require_once TCPDF_PATH . 'tcpdf_barcodes_2d.php';

            $barcode = new TCPDF2DBarcode($shortener->short_url, 'QRCODE,L');

            dol_mkdir($conf->easyurl->multidir_output[$conf->entity] . '/shortener/' . $shortener->ref . '/qrcode/');
            $file = $conf->easyurl->multidir_output[$conf->entity] . '/shortener/' . $shortener->ref . '/qrcode/' . 'barcode_' . $shortener->ref . '.png';

            $imageData = $barcode->getBarcodePngData();
            $imageData = imagecreatefromstring($imageData);
            imagepng($imageData, $file);

            setEventMessages($langs->trans('SetEasyURLSuccess'), []);
            return 1;
        } else {
            setEventMessages($langs->trans('SetEasyURLErrors'), [$data->message], 'errors');
            return $data;
        }
    } else {
        return -1;
    }
}

/**
 * get easy url link
 *
 * @param  string       $shortUrl Short url
 * @return int|stdClass           0 < on error, data object on success
 */
function get_easy_url_link(string $shortUrl)
{
    $curlPostFields = [
        'action'   => 'url-stats',
        'shorturl' => $shortUrl
    ];
    return init_easy_url_curl($curlPostFields);
}

/**
 * Update easy url link
 *
 * @param  CommonObject $object Object
 * @return int|stdClass         0 < on error, data object on success
 */
function update_easy_url_link(CommonObject $object)
{
    $curlPostFields = [
        'action'   => 'update',
        'shorturl' => $object->short_url,
        'url'      => $object->original_url
    ];
    return init_easy_url_curl($curlPostFields);
}

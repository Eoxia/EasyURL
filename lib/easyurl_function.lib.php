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
 * @param  array            $curlPostFields cURL post fields
 * @param  string           $urlMethod      Url method
 * @return CurlHandle|false $ch             cURL handle on success, false on errors
 */
function init_easy_url_curl(array $curlPostFields, string $urlMethod = 'yourls')
{
    // Init the CURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, getDolGlobalString('EASYURL_URL_' . dol_strtoupper($urlMethod) . '_API'));
    curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
    switch ($urlMethod) {
        case 'yourls' :
            // Data to POST
            $defaultCurlPostFields = [
                'signature' => getDolGlobalString('EASYURL_SIGNATURE_TOKEN_YOURLS_API'),
                'format'    => 'json'
            ];
            $curlPostFields = array_merge($curlPostFields, $defaultCurlPostFields);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPostFields);
            break;
        case 'wordpress' :
            break;
    }

    return $ch;
}

/**
 * Get the url method to use : the one asked, else the one set in the module setup
 *
 * @param  string $urlMethod Url method asked, empty for the default one
 * @return string            Url method (yourls, saturne)
 */
function easy_url_method(string $urlMethod = ''): string
{
    return dol_strlen($urlMethod) > 0 ? $urlMethod : getDolGlobalString('EASYURL_URL_METHOD', 'yourls');
}

/**
 * Load the Saturne short URL class
 *
 * @return bool True if Saturne provides short URLs (s/ entry point), false if it is too old
 */
function easy_url_load_saturne(): bool
{
    if (!is_file(__DIR__ . '/../../saturne/s/index.php')) {
        return false;
    }

    require_once __DIR__ . '/../../saturne/class/saturneredirection.class.php';

    return true;
}

/**
 * Shorten an url with the Saturne short URL, answer shaped as the YOURLS API one
 *
 * @param  string   $url     Original url
 * @param  string   $keyword Wanted code, a random one is used when empty, invalid or already used
 * @return stdClass          {status: success, shorturl} or {status: fail, message}
 * @throws Exception
 */
function easy_url_saturne_shorten(string $url, string $keyword = ''): stdClass
{
    global $db, $langs, $user;

    $saturneRedirection = new SaturneRedirection($db);

    $shortUrl = '';
    if (dol_strlen($keyword) > 0) {
        $shortUrl = $saturneRedirection->createShortUrl($url, $user, $keyword);
    }
    if ($shortUrl === '') {
        $shortUrl = $saturneRedirection->createShortUrl($url, $user);
    }

    $data = new stdClass();
    if ($shortUrl === '') {
        $data->status  = 'fail';
        $data->message = $langs->trans($saturneRedirection->error ?: 'Error');
    } else {
        $data->status   = 'success';
        $data->shorturl = $shortUrl;
    }

    return $data;
}

/**
 * Set easy url link
 *
 * @param  CommonObject $object       Object
 * @param  string       $urlType      Url type
 * @param  string       $urlMethod    Url method, empty for the one set in the module setup
 * @param  string       $errorMessage Error detail, filled when the call fails (transport or API)
 * @return int                        1 on success, 0 if nothing attempted (method not configured), < 0 on error
 */
function set_easy_url_link(CommonObject $object, string $urlType, string $urlMethod = '', string &$errorMessage = '')
{
    global $conf, $langs, $user;

    $urlMethod        = easy_url_method($urlMethod);
    $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
    if ($urlMethod == 'saturne') {
        $checkConf = easy_url_load_saturne();
    } else {
        $checkConf = getDolGlobalString('EASYURL_URL_' . dol_strtoupper($urlMethod) . '_API') && getDolGlobalString('EASYURL_SIGNATURE_TOKEN_' . dol_strtoupper($urlMethod) . '_API');
    }
    if ((($urlType == 'payment' && $useOnlinePayment) || $urlType == 'signature' || $urlType == 'none') && $checkConf) {
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
                if (property_exists($object, 'original_url') && dol_strlen($object->original_url) > 0) {
                    $onlineUrl = $object->original_url;
                } else {
                    $onlineUrl = getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL');
                }
                break;
        }

        if (property_exists($object, 'custom_easyurl_keyword') && !empty($object->custom_easyurl_keyword)) {
            $keyword = dol_sanitizeFileName($object->custom_easyurl_keyword);
        } else {
            $keyword = dol_sanitizeFileName(dol_strtolower($conf->global->MAIN_INFO_SOCIETE_NOM . '-' . $object->ref) . (getDolGlobalInt('EASYURL_USE_SHA_URL') ? '-' . generate_random_id(8) : ''));
        }
        
        if (property_exists($object, 'custom_easyurl_title') && !empty($object->custom_easyurl_title)) {
            $title = $object->custom_easyurl_title;
        } else {
            $title = $keyword;
        }

        if ($urlMethod == 'saturne') {
            // Only a keyword chosen by the user becomes the code,
            // the default one would lengthen the QR code for nothing
            $hasCustomKeyword = property_exists($object, 'custom_easyurl_keyword') && !empty($object->custom_easyurl_keyword);
            $customKeyword    = $hasCustomKeyword ? $keyword : '';

            $rawData = '';
            $data    = easy_url_saturne_shorten($onlineUrl, $customKeyword);
        } else {
            $curlPostFields = [
                'action'  => 'shorturl',
                'title'   => $title,
                'keyword' => $keyword,
                'url'     => $onlineUrl
            ];
            $ch = init_easy_url_curl($curlPostFields, $urlMethod);

            // Fetch and return content
            $rawData = curl_exec($ch);
            $errno   = curl_errno($ch);
            $error   = curl_error($ch);
            curl_close($ch);

            // Transport failure (network, DNS, timeout, ...): curl_exec() returns false. Must NOT be treated as a success
            if ($rawData === false || $errno) {
                $errorMessage = $error;
                dol_syslog('set_easy_url_link: cURL transport error (errno=' . $errno . ', error=' . $error . ')', LOG_ERR);
                setEventMessages($langs->trans('SetEasyURLErrors'), [$errorMessage], 'errors');
                return -1;
            }

            // Do something with the result
            $data = json_decode($rawData);
        }

        if (is_object($data) && isset($data->status) && $data->status == 'success') {
            if ($urlType != 'none') {
                $object->array_options['options_easy_url_' . $urlType . '_link'] = $data->shorturl;
                $object->updateExtraField('easy_url_' . $urlType . '_link');
                setEventMessage($langs->trans('SetEasyURLSuccess'));
            } else {
                // Shortener object in 100% of cases
                $object->status       = $object::STATUS_VALIDATED;
                $object->label        = $title;
                $object->short_url    = $data->shorturl;
                $object->original_url = $onlineUrl;
                $object->update($user, true);

                require_once TCPDF_PATH . 'tcpdf_barcodes_2d.php';

                $barcode = new TCPDF2DBarcode($object->short_url, 'QRCODE,L');

                dol_mkdir($conf->easyurl->multidir_output[$conf->entity] . '/shortener/' . $object->ref . '/qrcode/');
                $file = $conf->easyurl->multidir_output[$conf->entity] . '/shortener/' . $object->ref . '/qrcode/' . 'barcode_' . $object->ref . '.png';

                $imageData = $barcode->getBarcodePngData();
                $imageData = imagecreatefromstring($imageData);
                imagepng($imageData, $file);
            }
            return 1;
        }

        // Logical error returned by the shortener API (auth refused, invalid/duplicate keyword, ...)
        $errorMessage = (is_object($data) && isset($data->message)) ? $data->message : $rawData;
        dol_syslog('set_easy_url_link: shortener API returned an error: ' . $errorMessage, LOG_ERR);
        setEventMessages($langs->trans('SetEasyURLErrors'), [$errorMessage], 'errors');

        return -1;
    }

    // Nothing attempted: the required configuration for this method/type is missing
    return 0;
}

/**
 * get easy url link
 *
 * @param  CommonObject $object  Object
 * @param  string       $urlType Url type
 * @return stdClass|int          0 < on error, data object on success
 */
function get_easy_url_link(CommonObject $object, string $urlType)
{
    // Saturne short URLs have no click statistics : an existing one is a valid one
    $shortUrl = (string) ($object->array_options['options_easy_url_' . $urlType . '_link'] ?? '');
    $isSaturneUrl = dol_strlen($shortUrl) > 0 && easy_url_load_saturne() && SaturneRedirection::getCodeFromShortUrl($shortUrl) !== '';
    if ($isSaturneUrl) {
        $data               = new stdClass();
        $data->statusCode   = 200;
        $data->link         = new stdClass();
        $data->link->clicks = 0;

        return $data;
    }

    $useOnlinePayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
    $checkConf        = getDolGlobalString('EASYURL_URL_YOURLS_API') && getDolGlobalString('EASYURL_SIGNATURE_TOKEN_YOURLS_API');
    if ((($urlType == 'payment' && $useOnlinePayment) || $urlType == 'signature' || $urlType == 'all') && $checkConf) {
        $object->fetch($object->id);

        $curlPostFields = [
            'action'   => 'url-stats',
            'shorturl' => $object->array_options['options_easy_url_' . $urlType . '_link']
        ];
        $ch = init_easy_url_curl($curlPostFields);

        // Fetch and return content
        $data = curl_exec($ch);
        curl_close($ch);

        // Do something with the result
        return json_decode($data);
    } else {
        return -1;
    }
}

/**
 * Update easy url link
 *
 * @param  CommonObject $object Object
 * @return int                  0 < on error, 1 = statusCode 200, 0 = other statusCode (ex : 404)
 */
function update_easy_url_link(CommonObject $object): int
{
    global $db, $langs, $user;

    // A Saturne short URL keeps its code, only its target changes : a printed QR code stays valid
    if (easy_url_load_saturne()) {
        $code = SaturneRedirection::getCodeFromShortUrl((string) $object->short_url);
        if ($code !== '') {
            $saturneRedirection = new SaturneRedirection($db);
            if ($saturneRedirection->updateTargetUrl($code, (string) $object->original_url, $user) > 0) {
                return 1;
            }

            $object->error    = $langs->trans($saturneRedirection->error ?: 'Error');
            $object->errors[] = $object->error;

            return 0;
        }
    }

    $curlPostFields = [
        'action'   => 'update',
        'shorturl' => $object->short_url,
        'url'      => $object->original_url
    ];
    $ch = init_easy_url_curl($curlPostFields);

    // Fetch and return content
    $data    = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    // Do something with the result
    $data = json_decode($data);
    if (is_object($data) && $data->statusCode == 200) {
        return 1;
    }

    // Propagate the real error to the caller so it can be displayed to the user
    $statusCode = (is_object($data) && isset($data->statusCode)) ? $data->statusCode : '';
    $apiMessage = '';
    if (is_object($data)) {
        foreach ((array) $data as $key => $value) {
            if (trim($key) === 'simple' && dol_strlen($value) > 0) { // YOURLS renvoie une clé "simple " (avec un espace) contenant le message lisible
                $apiMessage = $value;
                break;
            }
        }
        if (empty($apiMessage) && !empty($data->message)) {
            $apiMessage = $data->message;
        }
    }
    if (empty($apiMessage)) {
        $apiMessage = (dol_strlen($curlErr) > 0 ? $curlErr : 'No response from URL shortener API');
    }

    $object->error    = (dol_strlen($statusCode) > 0 ? '[' . $statusCode . '] ' : '') . $apiMessage;
    $object->errors[] = $object->error;

    return 0;
}

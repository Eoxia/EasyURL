<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    public/shortener/public_shortener.php
 * \ingroup easyurl
 * \brief   Public page to assign shortener
 */

if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', 1);
}
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', 1);
}
if (!defined('NOCSRFCHECK')) {  // We accept to go on this page from external website
    define('NOCSRFCHECK', 1);
}
if (!defined('NOIPCHECK')) {    // Do not check IP defined into conf $dolibarr_main_restrict_ip
    define('NOIPCHECK', 1);
}
if (!defined('NOBROWSERNOTIF')) {
    define('NOBROWSERNOTIF', 1);
}

// Load EasyURL environment
if (file_exists('../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../easyurl.main.inc.php';
} elseif (file_exists('../../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../../easyurl.main.inc.php';
} else {
    die('Include of easyurl main fails');
}

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/productlot.class.php';

// Load EasyURL libraries
require_once __DIR__ . '/../../class/shortener.class.php';
require_once __DIR__ . '/../../lib/easyurl_function.lib.php';

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$trackId = GETPOST('track_id', 'alpha');
$action  = GETPOST('action', 'aZ09');
$entity  = GETPOST('entity');

// Initialize technical objects
$object = new Shortener($db);

$objectId = 0;
if (!empty($trackId)) {
    $objectDataJson = base64_decode($trackId);
    $objectData     = json_decode($objectDataJson);
    $linkedObject   = null;
    if (!empty($objectData)) {
        $objectType = $objectData->type;
        $objectId   = $objectData->id;

        $linkedObject = new $objectType($db);
    } else {
        //@todo : Besoin du selecteur d'object pour le moment on va forcer ProductLot
        $linkedObject = new ProductLot($db);
    }
} else {
    //@todo : Besoin du selecteur d'object pour le moment on va forcer ProductLot
    $linkedObject = new ProductLot($db);
}

$hookmanager->initHooks(['publicshortener', 'saturnepublicinterface']); // Note that conf->hooks_modules contains array

if (!isModEnabled('multicompany')) {
    $entity = $conf->entity;
}

$conf->setEntityValues($db, $entity);

//@todo notice d'erreur pour le track_id
// Load linkable elements
$linkableElement = [];
if (is_object($linkedObject)) {
    $linkableElements = saturne_get_objects_metadata();
    $linkableElement  = $linkableElements[$linkedObject->element];
}

$permissionToAssign = $user->hasRight('easyurl', 'shortener', 'assign');

/*
 * Actions
 */

$parameters = [];
$resHook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $project may have been modified by some hooks
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {
    if ($action == 'assign_qrcode' && $permissionToAssign && is_object($linkedObject)) {
        $fkElementID = GETPOSTINT('fk_element');
        $shortenerID = GETPOSTINT('shortener');

        $linkedObject->fetch($fkElementID);
        $object->fetch($shortenerID);

        if ($linkedObject->id > 0 && $object->id > 0) {
            $object->element_type = 'productlot';
            $object->fk_element   = $linkedObject->id;
            $object->status       = Shortener::STATUS_ASSIGN;
            $object->type         = 0; // TODO : Changer ça pour mettre une vrai valeur du dico ?

            $publicControlInterfaceUrl = dol_buildpath('custom/digiquali/public/control/public_control_history.php?track_id=' . $linkedObject->array_options['options_control_history_link'] . '&entity=' . $conf->entity, 3);
            $object->original_url      = $publicControlInterfaceUrl;

            $result = update_easy_url_link($object);
            if ($result > 0) {
                $object->update($user);

                $linkedObject->array_options['options_easy_url_all_link'] = $object->short_url;
                $linkedObject->updateExtraField('easy_url_all_link');

                setEventMessages($langs->transnoentities('AssignQRCodeSuccess', $object->label, $langs->transnoentities($linkableElement['langs']), $linkedObject->{$linkableElement['name_field']}), []);
            } else {
                setEventMessages('AssignQRCodeErrors', [], 'errors');
            }
        } else {
            setEventMessages('AssignQRCodeErrors', [], 'errors');
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . (!empty($trackId) ? '?track_id=' .  $trackId . '&' : '?') . 'entity=' . $entity);
        exit;
    }
}

/*
 * View
 */

$title = $langs->trans('PublicInterfaceObject', $langs->transnoentities('OfAssignShortener')) . '</a>';

$conf->dol_hide_topmenu  = 1;
$conf->dol_hide_leftmenu = 1;

saturne_header(1, '', $title,  '', '', 0, 0, [], [], '', 'page-public-card page-public-shortener');

require_once __DIR__ . '/../frontend/assign_qrcode_view.tpl.php';

llxFooter('', 'public');
$db->close();

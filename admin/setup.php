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
 * \file    admin/setup.php
 * \ingroup easyurl
 * \brief   EasyURL setup page
 */

// Load EasyURL environment
if (file_exists('../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../easyurl.main.inc.php';
} elseif (file_exists('../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../easyurl.main.inc.php';
} else {
    die('Include of easyurl main fails');
}

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

// Load EasyURL libraries
require_once __DIR__ . '/../lib/easyurl.lib.php';
require_once __DIR__ . '/../lib/easyurl_function.lib.php';

// Global variables definitions
global $conf, $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs(['admin']);

// Get parameters
$action     = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize view objects
$form = new Form($db);

// Security check - Protection if external user
$permissionToRead = $user->rights->easyurl->adminpage->read;
saturne_check_access($permissionToRead);

/*
 * Actions
 */

if ($action == 'set_config') {
    $APISettings = ['url_yourls_api', 'signature_token_yourls_api', 'default_original_url'];
    foreach ($APISettings as $APISetting) {
        $APISettingValue = GETPOST($APISetting);
        $confName        = 'EASYURL_' . dol_strtoupper($APISetting);
        if (dol_strlen($APISettingValue) > 0) {
            dolibarr_set_const($db, $confName, $APISettingValue, 'chaine', 0, '', $conf->entity);
        }
    }

    $curlPostFields = ['action' => 'version'];
    $data           = init_easy_url_curl($curlPostFields);
    if (empty($data) || (isset($data->errorCode) && $data->errorCode != 200)) {
        setEventMessages('ErrorEasyURLAPIConfig', [], 'errors');
    } else {
        setEventMessages('SavedConfig', []);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

/*
 * View
 */

$title    = $langs->trans('ModuleSetup', 'EasyURL');
$help_url = 'FR:Module_EasyURL';

saturne_header(0,'', $title, $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ?: DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

// Configuration header
$head = easyurl_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $title, -1, 'easyurl_color@easyurl');

print load_fiche_titre($langs->trans('Config'), '', '');

print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="set_config">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Parameters') . '</td>';
print '<td>' . $langs->trans('Description') . '</td>';
print '<td>' . $langs->trans('Value') . '</td>';
print '</tr>';

print '<tr class="oddeven"><td><label for="url_yourls_api">' . $langs->trans('URLYourlsAPI') . '</label></td>';
print '<td>' . $langs->trans('URLYourlsAPIDescription') . '</td>';
print '<td><input class="minwidth300" type="text" name="url_yourls_api" value="' . $conf->global->EASYURL_URL_YOURLS_API . '" required></td>';
print '</td></tr>';

print '<tr class="oddeven"><td><label for="signature_token_yourls_api">' . $langs->trans('SignatureTokenYourlsAPI') . '</label></td>';
print '<td>' . $langs->trans('SignatureTokenYourlsAPIDescription') . '</td>';
print '<td><input class="minwidth300" type="password" name="signature_token_yourls_api" value="' . $conf->global->EASYURL_SIGNATURE_TOKEN_YOURLS_API . '" required></td>';
print '</td></tr>';

print '<tr class="oddeven"><td><label for="default_original_url">' . $langs->trans('DefaultOriginalUrl') . '</label></td>';
print '<td>' . $langs->trans('DefaultOriginalUrlDescription') . '</td>';
print '<td><input class="minwidth300" type="text" name="default_original_url" value="' . $conf->global->EASYURL_DEFAULT_ORIGINAL_URL . '"></td>';
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('UseShaUrl') . '</td>';
print '<td>' . $langs->trans('UseShaUrlDescription') . '</td>';
print '<td>';
print ajax_constantonoff('EASYURL_USE_SHA_URL');
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('ShowAPIInfos') . '</td>';
print '<td>' . $langs->trans('ShowAPIInfosDescription') . '</td>';
print '<td>';
print ajax_constantonoff('EASYURL_SHOW_API_INFOS');
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('AutomaticEasyUrlGeneration') . '</td>';
print '<td>' . $langs->trans('AutomaticEasyUrlGenerationDescription') . '</td>';
print '<td>';
print ajax_constantonoff('EASYURL_AUTOMATIC_GENERATION');
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('ManualEasyUrlGeneration') . '</td>';
print '<td>' . $langs->trans('ManualEasyUrlGenerationDescription') . '</td>';
print '<td>';
print ajax_constantonoff('EASYURL_MANUAL_GENERATION');
print '</td></tr>';

print '</table>';
print $form->buttonsSaveCancel('Save', '');
print '</form>';

$db->close();
llxFooter();

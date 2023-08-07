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
 * \ingroup tinyurl
 * \brief   TinyURL setup page
 */

// Load TinyURL environment
if (file_exists('../tinyurl.main.inc.php')) {
    require_once __DIR__ . '/../tinyurl.main.inc.php';
} elseif (file_exists('../../tinyurl.main.inc.php')) {
    require_once __DIR__ . '/../../tinyurl.main.inc.php';
} else {
    die('Include of tinyurl main fails');
}

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

// Load TinyURL libraries
require_once __DIR__ . '/../lib/tinyurl.lib.php';

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
$permissionToRead = $user->rights->tinyurl->adminpage->read;
saturne_check_access($permissionToRead);

/*
 * Actions
 */

if ($action == 'set_config') {
    $URLYourlsAPI            = GETPOST('url_yourls_api');
    $signatureTokenYourlsAPI = GETPOST('signature_token_yourls_api');

    if (dol_strlen($URLYourlsAPI) > 0) {
        dolibarr_set_const($db, 'TINYURL_URL_YOURLS_API', $URLYourlsAPI, 'chaine', 0, '', $conf->entity);
    }
    if (dol_strlen($signatureTokenYourlsAPI) > 0) {
        dolibarr_set_const($db, 'TINYURL_SIGNATURE_TOKEN_YOURLS_API', $signatureTokenYourlsAPI, 'chaine', 0, '', $conf->entity);
    }

    setEventMessage('SavedConfig');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}



/*
 * View
 */

$title    = $langs->trans('ModuleSetup', 'TinyURL');
$help_url = 'FR:Module_TinyURL';

saturne_header(0,'', $title, $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ?: DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

// Configuration header
$head = tinyurl_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $title, -1, 'tinyurl_color@tinyurl');

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
print '<td><input type="text" name="url_yourls_api" value="' . $conf->global->TINYURL_URL_YOURLS_API . '"></td>';
print '</td></tr>';

print '<tr class="oddeven"><td><label for="signature_token_yourls_api">' . $langs->trans('SignatureTokenYourlsAPI') . '</label></td>';
print '<td>' . $langs->trans('SignatureTokenYourlsAPIDescription') . '</td>';
print '<td><input type="password" name="signature_token_yourls_api" value="' . $conf->global->TINYURL_SIGNATURE_TOKEN_YOURLS_API . '"></td>';
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('UseShaUrl') . '</td>';
print '<td>' . $langs->trans('UseShaUrlDescription') . '</td>';
print '<td>';
print ajax_constantonoff('TINYURL_USE_SHA_URL');
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('AutomaticTinyUrlGeneration') . '</td>';
print '<td>' . $langs->trans('AutomaticTinyUrlGenerationDescription') . '</td>';
print '<td>';
print ajax_constantonoff('TINYURL_AUTOMATIC_GENERATION');
print '</td></tr>';

print '<tr class="oddeven"><td>' . $langs->trans('ManualTinyUrlGeneration') . '</td>';
print '<td>' . $langs->trans('ManualTinyUrlGenerationDescription') . '</td>';
print '<td>';
print ajax_constantonoff('TINYURL_MANUAL_GENERATION');
print '</td></tr>';

print '</table>';
print $form->buttonsSaveCancel('Save', '');
print '</form>';

$db->close();
llxFooter();

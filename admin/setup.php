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

/*
 * View
 */

$title    = $langs->trans('ModuleSetup', 'TinyURL');
$help_url = 'FR:Module_TinyURL';

saturne_header(0,'', $title, $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ?: DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($title, $linkback, 'tinyurl_color@tinyurl');

// Configuration header
$head = tinyurl_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $title, -1, 'tinyurl_color@tinyurl');

$db->close();
llxFooter();

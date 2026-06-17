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
 * \file    view/easyurltools.php
 * \ingroup easyurl
 * \brief   Tools page of EasyURL top menu
 */

// Load EasyURL environment
if (file_exists('../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../easyurl.main.inc.php';
} elseif (file_exists('../../easyurl.main.inc.php')) {
    require_once __DIR__ . '/../../easyurl.main.inc.php';
} else {
    die('Include of easyurl main fails');
}

// Load EasyURL libraries
require_once __DIR__ . '/../class/shortener.class.php';
require_once __DIR__ . '/../class/exportshortenerdocument.class.php';
require_once __DIR__ . '/../lib/easyurl_function.lib.php';

// Global variables definitions
global $conf, $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$action = (GETPOSTISSET('action') ? GETPOST('action', 'aZ09') : 'view');

// Initialize technical objects
$shortener               = new Shortener($db);
$exportShortenerDocument = new ExportShortenerDocument($db);

// Initialize view objects
$form = new Form($db);

// Security check - Protection if external user
$permissionToRead = $user->rights->easyurl->adminpage->read;
$permissionToAdd  = $user->rights->easyurl->shortener->write;
saturne_check_access($permissionToRead);

/*
 * Actions
 */

if ($action == 'generate_url' && $permissionToAdd) {
    $urlParametersOut = '';
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data)) {
        $urlMethode    = $data['url_methode'];
        $originalUrl   = $data['original_url'];
        $urlParameters = $data['url_parameters'];
        $urlTitle      = isset($data['url_title']) ? $data['url_title'] : '';

        if (dol_strlen($originalUrl) > 0 || dol_strlen(getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL')) > 0) {
            $shortener->ref = $shortener->getNextNumRef();
            if (dol_strlen($originalUrl) > 0) {
                $shortener->original_url = $originalUrl;
            } else {
                $shortener->original_url = getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL');
            }

            if (!empty($urlParameters)) {
                $customKeyword = $urlParameters;
                $customKeyword = str_ireplace(
                    ['{AAAAMMJJ}', '{AAMMJJ}', '{AMMJJ}', '{AAAAMM}', '{AAMM}'],
                    [date('Ymd'), date('ymd'), date('ymd'), date('Ym'), date('ym')],
                    $customKeyword
                );
                
                $currentIndex = GETPOSTINT('nb_url');
                if (preg_match('/\{(\d+)\}/', $customKeyword, $matches)) {
                    $originalMatch = $matches[0];
                    $digitsStr = $matches[1];
                    $padLength = strlen($digitsStr);
                    $startValue = (int)$digitsStr;
                    $newValue = str_pad((string)($startValue + $currentIndex - 1), $padLength, '0', STR_PAD_LEFT);
                    $customKeyword = str_replace($originalMatch, $newValue, $customKeyword);
                }
                
                $shortener->custom_easyurl_keyword = dol_strtolower($customKeyword);
            }
            if (!empty($urlTitle)) {
                $customTitle = $urlTitle;
                $customTitle = str_ireplace(
                    ['{AAAAMMJJ}', '{AAMMJJ}', '{AMMJJ}', '{AAAAMM}', '{AAMM}'],
                    [date('Ymd'), date('ymd'), date('ymd'), date('Ym'), date('ym')],
                    $customTitle
                );
                
                $currentIndex = GETPOSTINT('nb_url');
                if (preg_match('/\{(\d+)\}/', $customTitle, $matches)) {
                    $originalMatch = $matches[0];
                    $digitsStr = $matches[1];
                    $padLength = strlen($digitsStr);
                    $startValue = (int)$digitsStr;
                    $newValue = str_pad((string)($startValue + $currentIndex - 1), $padLength, '0', STR_PAD_LEFT);
                    $customTitle = str_replace($originalMatch, $newValue, $customTitle);
                }
                
                $shortener->custom_easyurl_title = $customTitle;
            }
            
            $shortener->methode = $urlMethode;

            $shortener->create($user);

            // UrlType : none because we want mass generation url (all can be use but need to change this code)
            $result = set_easy_url_link($shortener, 'none', $urlMethode);
            if (!empty($result) && is_object($result)) {
                $logDir = $conf->easyurl->multidir_output[$conf->entity] . '/logs';
                if (!is_dir($logDir)) { dol_mkdir($logDir); }
                $logFile = $logDir . '/generation_errors.log';
                $logContent = date('Y-m-d H:i:s') . " - URL " . GETPOSTINT('nb_url') . " - " . $result->message . "\n";
                file_put_contents($logFile, $logContent, FILE_APPEND);
                
                $errMsg = urlencode($result->message);
                $urlParametersOut .= '?success=false&nb_url=' . GETPOST('nb_url') . '&successType=shortener&error_msg=' . $errMsg . '&failed_keyword=' . urlencode($shortener->custom_easyurl_keyword);
            } else {
                $urlParametersOut .= '?success=true&nb_url=' . GETPOST('nb_url') . '&successType=shortener&generated_keyword=' . urlencode($shortener->short_url);
            }
        } else {
            $urlParametersOut .= '?success=false&nb_url=' . GETPOST('nb_url') . '&successType=shortener';
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . $urlParametersOut);
    exit;
}

if ($action == 'generate_export' && $permissionToAdd) {
    $numberingModules  = [$exportShortenerDocument->module . 'documents/' . $exportShortenerDocument->element  => getDolGlobalString('EASYURL_EXPORTSHORTENERDOCUMENT_ADDON')];
    list($refModName)  = saturne_require_objects_mod($numberingModules, 'easyurl');
    $objectDocumentRef = $refModName->getNextValue($exportShortenerDocument);

    $exportShortenerDocument->ref = $objectDocumentRef;

    $exportShortenerDocument->create($user);

    $nbUrl      = GETPOST('nb_url');
    $shorteners = $shortener->fetchAll('DESC', 'rowid', $nbUrl);
    if (is_array($shorteners) && !empty($shorteners)) {
        $data = [
            'original_url'         => current($shorteners)->original_url,
            'last_shortener_id'    => current($shorteners)->id,
            'first_shortener_id'   => end($shorteners)->id,
            'number_shortener_url' => $nbUrl
        ];
        $exportShortenerDocument->ref  = $objectDocumentRef;
        $exportShortenerDocument->json = json_encode($data);
        $exportShortenerDocument->generateFile();
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?success=true&successType=export');
    exit;
}

/*
 * View
 */

$title   = $langs->trans('Tools');
$helpUrl = 'FR:Module_EasyURL';

saturne_header(0,'', $title, $helpUrl);

print load_fiche_titre($title, '', 'wrench');

if (!getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL')) : ?>
    <div class="wpeo-notice notice-warning">
        <div class="notice-content">
            <div class="notice-title">
                <a href="<?php echo dol_buildpath('/custom/easyurl/admin/setup.php', 1); ?>"><strong><?php echo $langs->trans('DefaultOriginalUrlConfiguration'); ?></strong></a>
            </div>
        </div>
    </div>
<?php endif;
if (!getDolGlobalString('EASYURL_URL_YOURLS_API') || !getDolGlobalString('EASYURL_SIGNATURE_TOKEN_YOURLS_API')) : ?>
    <div class="wpeo-notice notice-warning">
        <div class="notice-content">
            <div class="notice-title">
                <a href="<?php echo dol_buildpath('/custom/easyurl/admin/setup.php', 1); ?>"><strong><?php echo $langs->trans('ApiCredentialsNotCongirate'); ?></strong></a>
            </div>
        </div>
    </div>
<?php endif;

$translations = [
    'ExportGenerating' => $langs->transnoentities('ExportGenerating'),
    'ExportError'      => $langs->transnoentities('ExportError'),
    'YouGenerated'     => $langs->transnoentities('YouGenerated'),
    'UrlWithSuccess'   => $langs->transnoentities('UrlWithSuccess'),
    'Success'          => $langs->transnoentities('Success'),
    'Error'            => $langs->transnoentities('Error'),
];
print saturne_show_notice('', '', 'success', 'notice-infos', 0, 1, '', $translations);

print load_fiche_titre($langs->trans('GenerateUrlManagement'), '', '');

print '<form name="generate-url-from" id="generate-url-from">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="generate_url">';
if (GETPOSTISSET('success')) {
    print '<input type="hidden" name="success" value="' . GETPOST('success') . '">';
}
if (GETPOSTISSET('error_msg')) {
    print '<input type="hidden" name="error_msg" value="' . dol_escape_htmltag(urldecode(GETPOST('error_msg'))) . '">';
}
if (GETPOSTISSET('failed_keyword')) {
    print '<input type="hidden" name="failed_keyword" value="' . dol_escape_htmltag(urldecode(GETPOST('failed_keyword'))) . '">';
}
if (GETPOSTISSET('generated_keyword')) {
    print '<input type="hidden" name="generated_keyword" value="' . dol_escape_htmltag(urldecode(GETPOST('generated_keyword'))) . '">';
}

$displayAstuce = GETPOSTISSET('error_msg') ? 'block' : 'none';
print '<div class="wpeo-notice notice-warning" style="display: ' . $displayAstuce . '; margin-bottom: 15px;"><div class="notice-content"><div class="notice-title"><strong>Astuce YOURLS</strong></div><div class="notice-desc">Si vous générez plusieurs liens vers la même URL d\'origine, YOURLS bloquera la création (Erreur "URL already exists"). Pour autoriser les doublons, vous devez définir <code>define(\'YOURLS_UNIQUE_URLS\', false);</code> dans le fichier <code>user/config.php</code> de votre serveur YOURLS.</div></div></div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Parameters') . '</td>';
print '<td>' . $langs->trans('Description') . '</td>';
print '<td>' . $langs->trans('Value') . '</td>';
print '</tr>';

$urlMethode = ['yourls' => 'YOURLS', 'wordpress' => 'WordPress'];
print '<tr class="oddeven"><td>';
print $langs->trans('UrlMethode');
print '</td><td>';
print $langs->trans('UrlMethodeDescription');
print '<td>';
print $form::selectarray('url_methode', $urlMethode, 'yourls');
print '</td></tr>';

print '<tr class="oddeven"><td class="fieldrequired"><label for="nb_url">' . $langs->trans('NbUrl') . '</label></td>';
print '<td>' . $langs->trans('NbUrlDescription') . '</td>';
print '<td><input class="minwidth100" type="number" name="nb_url" min="1" required></td>';
print '</tr>';

print '<tr class="oddeven"><td><label for="original_url">' . $langs->trans('OriginalUrl') . '</label></td>';
print '<td>' .  $langs->trans('OriginalUrlDescription') . (getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL') ? $langs->trans('OriginalUrlMoreDescription', getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL')) : '') . '</td>';
print '<td><input class="minwidth300" type="text" name="original_url" value="' . getDolGlobalString('EASYURL_DEFAULT_ORIGINAL_URL') . '"></td>';
print '</tr>';

print '<tr class="oddeven"><td><label for="url_title">' . $langs->trans('EasyUrlTitle') . '</label></td>';
print '<td>' . $langs->trans('EasyUrlTitleDescription') . '</td>';
print '<td><input class="minwidth300" type="text" name="url_title"></td>';
print '</tr>';

print '<tr class="oddeven"><td><label for="url_parameters">' . $langs->trans('UrlParameters') . '</label></td>';
print '<td>' . $langs->trans('UrlParametersDescription') . '</td>';
print '<td><input class="minwidth300" type="text" name="url_parameters"></td>';
print '</tr>';

print '</table>';
print '<div class="right">';
print $form->buttonsSaveCancel('Generate', '', [], 1);
print '</div>';
print '</form>';

print load_fiche_titre($langs->trans('GeneratedExport'), '', '');

// Logs button removed (now managed in the console popup)

print '<table class="noborder centpercent" id="shortener-export-table">';

print '<tr class="liste_titre">';
print '<td>' . $langs->trans('ExportId') . '</td>';
print '<td>' . $langs->trans('ExportNumber') . '</td>';
print '<td>' . $langs->trans('ExportStart') . '</td>';
print '<td>' . $langs->trans('ExportEnd') . '</td>';
print '<td>' . $langs->trans('ExportDate') . '</td>';
print '<td>' . $langs->trans('ExportOrigin') . '</td>';
print '<td>' . $langs->trans('ExportConsume') . '</td>';
print '<td>' . $langs->trans('Action') . '</td>';
print '</tr>';

$exportShortenerDocuments = $exportShortenerDocument->fetchAll('DESC', 'rowid', 0, 0, ['customsql' => 't.type="' . $exportShortenerDocument->element . '"']);
if (is_array($exportShortenerDocuments) && !empty($exportShortenerDocuments)) {
    foreach ($exportShortenerDocuments as $exportShortenerDocument) {
        $data = json_decode($exportShortenerDocument->json, true);
        if (!$data) {
            continue;
        }

        $shorteners = $shortener->fetchAll('', '', $data['number_shortener_url'], 0, ['customsql' => 't.rowid >=' . $data['first_shortener_id']]);
        if (is_array($shorteners) && !empty($shorteners)) {
            print '<tr class="oddeven">';
            print '<td>' . $exportShortenerDocument->ref . '</td>';
            print '<td>' . $data['number_shortener_url'] . '</td>';
            print '<td>' . $data['first_shortener_id'] . '</td>';
            print '<td>' . $data['last_shortener_id'] . '</td>';
            print '<td>' . dol_print_date($exportShortenerDocument->date_creation, 'dayhour', 'tzuser') . '</td>';
            print '<td>' . dol_print_url($data['original_url'], '_blank', 64, 1) . '</td>';
            print '<td>' . count(array_filter($shorteners, function ($elem) {return $elem->status == Shortener::STATUS_ASSIGN;})) . '</td>';

            $uploadDir = $conf->easyurl->multidir_output[$conf->entity ?? 1];
            $fileDir   = $uploadDir . '/' . $exportShortenerDocument->element;
            if (dol_is_file($fileDir . '/' . $exportShortenerDocument->last_main_doc)) {
                $documentUrl = DOL_URL_ROOT . '/document.php';
                $fileUrl     = $documentUrl . '?modulepart=easyurl&file=' . urlencode($exportShortenerDocument->element . '/' . $exportShortenerDocument->last_main_doc);
                print '<td><div><a class="marginleftonly" href="' . $fileUrl . '" download>' . img_picto($langs->trans('File') . ' : ' . $exportShortenerDocument->last_main_doc, 'fa-file-csv') . '</a></div></td>';
            }
            print '</tr>';
        }
    }
} else {
    print '<tr><td colspan="8"><span class="opacitymedium">' . $langs->trans('NoRecordFound') . '</span></td></tr>';
}

print '</table>';

$logFile = $conf->easyurl->multidir_output[$conf->entity] . '/logs/generation_errors.log';
$consoleInitialContent = '';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lines = array_slice($lines, -200); // Keep last 200 lines
    foreach ($lines as $line) {
        if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - (.*)$/', trim($line), $matches)) {
            $time = substr($matches[1], 11);
            $msg = $matches[2];
            $consoleInitialContent .= '<div class="eu-log-line"><span class="eu-log-time">' . $time . '</span><span class="eu-log-pfx">&gt;_</span><span class="eu-log-e">[HISTORIQUE KO] ' . dol_escape_htmltag($msg) . '</span></div>';
        } else {
            $consoleInitialContent .= '<div class="eu-log-line"><span class="eu-log-pfx">&gt;_</span><span class="eu-log-e">' . dol_escape_htmltag(trim($line)) . '</span></div>';
        }
    }
}

print '<style>
.eu-console-popup{position:fixed;bottom:0;right:24px;width:660px;max-width:calc(100vw - 48px);background:#0d1117;border:1px solid #30363d;border-bottom:none;border-radius:8px 8px 0 0;font-family:\'Consolas\',\'Courier New\',monospace;z-index:9999;box-shadow:0 -4px 20px rgba(0,0,0,.5);}
.eu-con-hd{display:flex;align-items:center;justify-content:space-between;padding:7px 14px;background:#161b22;border-bottom:1px solid #30363d;border-radius:8px 8px 0 0;cursor:pointer;user-select:none;}
.eu-con-title{color:#58a6ff;font-weight:700;font-size:.82em;letter-spacing:.5px;white-space:nowrap;}
.eu-con-acts{display:flex;gap:10px;align-items:center;font-size:.76em;color:#8b949e;flex-shrink:0;}
.eu-con-acts button{background:none;border:none;color:#8b949e;cursor:pointer;padding:0;font-family:inherit;font-size:1em;}
.eu-con-acts button:hover{color:#c9d1d9;}
.eu-con-sep{color:#30363d;}
.eu-con-body{height:260px;overflow-y:auto;padding:8px 14px;scroll-behavior:smooth;}
.eu-log-line{display:flex;gap:8px;margin-bottom:2px;font-size:.76em;line-height:1.5;}
.eu-log-time{color:#484f58;min-width:56px;flex-shrink:0;}
.eu-log-pfx{color:#58a6ff;flex-shrink:0;}
.eu-log-s{color:#3fb950;} .eu-log-e{color:#f85149;} .eu-log-w{color:#d29922;} .eu-log-i{color:#c9d1d9;}
</style>
<div class="eu-console-popup" id="eu-cp">
  <div class="eu-con-hd" onclick="jQuery(\'#eu-cb\').toggle();">
    <span style="display:flex;align-items:center;min-width:0;overflow:hidden;">
      <span class="eu-con-title">&gt;_ CONSOLE</span>
    </span>
    <span class="eu-con-acts" onclick="event.stopPropagation()">
      <span style="color:#3fb950"><span id="eu-count-ok">0</span> OK</span> / <span style="color:#f85149"><span id="eu-count-ko">' . $historyKoCount . '</span> KO</span>
      <span class="eu-con-sep">|</span>
      <select onchange="var url=new URL(window.location.href);url.searchParams.set(\'history_lines\', this.value);window.location.href=url.href;" style="background:transparent;color:#8b949e;border:1px solid #30363d;border-radius:4px;padding:0 2px;">
        <option value="50" ' . ($historyLines==50?'selected':'') . '>50 lignes</option>
        <option value="200" ' . ($historyLines==200?'selected':'') . '>200 lignes</option>
        <option value="500" ' . ($historyLines==500?'selected':'') . '>500 lignes</option>
      </select>
      <span class="eu-con-sep">|</span>
      <button onclick="window.open(document.URL.substring(0, document.URL.indexOf(\'/custom/easyurl/\')) + \'/document.php?modulepart=easyurl&file=logs/generation_errors.log\', \'_blank\')" title="Télécharger les logs d\'erreurs">&#11015; Logs</button><span class="eu-con-sep">|</span>
      <button onclick="jQuery(\'#eu-cb\').empty(); jQuery(\'#eu-count-ok\').text(\'0\'); jQuery(\'#eu-count-ko\').text(\'0\');">Vider</button><span class="eu-con-sep">|</span>
      <button onclick="jQuery(\'#eu-cb\').toggle()" title="Ouvrir / Fermer">&#9650;</button>
    </span>
  </div>
  <div class="eu-con-body" id="eu-cb" style="display:none">' . $consoleInitialContent . '</div>
</div>';

// End of page
llxFooter();
$db->close();

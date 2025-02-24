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
 * \file    class/actions_easyurl.class.php
 * \ingroup easyurl
 * \brief   EasyURL hook overload
 */

// Load EasyURL libraries
require_once __DIR__ . '/../lib/easyurl_function.lib.php';

/**
 * Class ActionsEasyurl
 */
class ActionsEasyurl
{
    /**
     * @var DoliDB Database handler
     */
    public DoliDB $db;

    /**
     * @var string Error code (or message)
     */
    public string $error = '';

    /**
     * @var array Errors.
     */
    public array $errors = [];

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public array $results = [];

    /**
     * @var string|null String displayed by executeHook() immediately after return
     */
    public ?string $resprints;

    /**
     * Constructor
     *
     *  @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * Overloading the addHtmlHeader function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function addHtmlHeader(array $parameters): int
    {
        if (isModEnabled('digiquali') && strpos($parameters['currentcontext'], 'publiccontrol') !== false) {
            $resourcesRequired = [
                'css' => '/custom/easyurl/css/easyurl.min.css',
                'js'  => '/custom/easyurl/js/easyurl.min.js'
            ];


            $out  = '<!-- Includes CSS added by module easyurl -->';
            $out .= '<link rel="stylesheet" type="text/css" href="' . dol_buildpath($resourcesRequired['css'], 1) . '">';

            $out .= '<!-- Includes JS added by module easyurl -->';
            $out .= '<script src="' . dol_buildpath($resourcesRequired['js'], 1) . '"></script>';

            $this->resprints = $out;
        }

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the printCommonFooter function : replacing the parent's function with the one below
     *
     * @param  array     $parameters Hook metadatas (context, etc...)
     * @return int                   0 < on error, 0 on success, 1 to replace standard code
     * @throws Exception
     */
    public function printCommonFooter(array $parameters): int
    {
        global $object;

        require_once __DIR__ . '/../../saturne/lib/object.lib.php';

        $objectsMetadata = saturne_get_objects_metadata();
        if (!empty($objectsMetadata)) {
            foreach ($objectsMetadata as $objectMetadata) {
                if ($objectMetadata['link_name'] == $object->element || $objectMetadata['tab_type'] == $object->element) {
                    if ($parameters['currentcontext'] == $objectMetadata['hook_name_card']) {

                        $jsPath = dol_buildpath('/saturne/js/saturne.min.js', 1);
                        print '<script src="' . $jsPath . '" ></script>';
                        $jsPath = dol_buildpath('/easyurl/js/easyurl.min.js', 1);
                        print '<script src="' . $jsPath . '" ></script>';

                        require_once __DIR__ . '/shortener.class.php';

                        $shortener = new Shortener($this->db);
                        $output = $shortener->displayObjectDetails($object); ?>
                        <script>
                            jQuery('.fichehalfright').first().append(<?php echo json_encode($output); ?>);
                        </script>
                        <?php
                    }
                }
            }
        }

        return 0; // or return 1 to replace standard code
    }

    /**
     *  Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param  array        $parameters Hook metadatas (context, etc...)
     * @param  CommonObject $object     Current object
     * @param  string       $action     Current action
     * @return int                      0 < on error, 0 on success, 1 to replace standard code
     */
    public function doActions(array $parameters, $object, string $action): int
    {
        global $conf, $langs, $user;

        require_once __DIR__ . '/../../saturne/lib/object.lib.php';

        $objectsMetadata = saturne_get_objects_metadata();
        if (!empty($objectsMetadata)) {
            foreach ($objectsMetadata as $objectMetadata) {
                if ($objectMetadata['link_name'] == $object->element || $objectMetadata['tab_type'] == $object->element) {
                    if ($parameters['currentcontext'] == $objectMetadata['hook_name_card']) {
                        if ($action == 'show_qrcode') {
                            require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

                            $data = json_decode(file_get_contents('php://input'), true);

                            $showQRCode = $data['showQRCode'];

                            $tabParam['EASYURL_SHOW_QRCODE'] = $showQRCode;

                            dol_set_user_param($this->db, $conf, $user, $tabParam);
                        }
                    }
                }
            }
        }

        if (isModEnabled('digiquali') && strpos($parameters['currentcontext'], 'publiccontrol') !== false) {
            $langs->load('easyurl@easyurl');

            $permissionToAssign = $user->hasRight('easyurl', 'shortener', 'assign');
            if ($action == 'assign_qrcode' && $permissionToAssign && is_object($parameters['linkedObject'])) {
                $fkElementID = GETPOSTINT('fk_element');
                $shortenerID = GETPOSTINT('shortenerId');

                require_once __DIR__ . '/shortener.class.php';

                $object = new Shortener($this->db);

                $parameters['linkedObject']->fetch($fkElementID);
                $object->fetch($shortenerID);

                if ($parameters['linkedObject']->id > 0 && $object->id > 0) {
                    $object->element_type = 'productlot';
                    $object->fk_element   = $parameters['linkedObject']->id;
                    $object->status       = Shortener::STATUS_ASSIGN;
                    $object->type         = 0; // TODO : Changer ça pour mettre une vrai valeur du dico ?

                    $publicControlInterfaceUrl = dol_buildpath('custom/digiquali/public/control/public_control_history.php?track_id=' . $parameters['trackId'] . '&entity=' . $conf->entity, 3);
                    $object->original_url      = $publicControlInterfaceUrl;

                    $result = update_easy_url_link($object);
                    if ($result > 0) {
                        $object->update($user);

                        $parameters['linkedObject']->array_options['options_easy_url_all_link'] = $object->short_url;
                        $parameters['linkedObject']->updateExtraField('easy_url_all_link');

                        setEventMessages($langs->transnoentities('AssignQRCodeSuccess', $object->label, $langs->transnoentities($parameters['linkableElement']['langs']), $parameters['linkedObject']->{$parameters['linkableElement']['name_field']}), []);
                    } else {
                        setEventMessages('AssignQRCodeErrors', [], 'errors');
                    }
                } else {
                    setEventMessages('AssignQRCodeErrors', [], 'errors');
                }

                header('Location: ' . $_SERVER['PHP_SELF'] . (!empty($parameters['trackId']) ? '?track_id=' .  $parameters['trackId'] . '&' : '?') . 'entity=' . $parameters['entity'] . '&route=assignQRCode');
                exit;
            }
            if ($action == 'show_qrcode') {
                require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

                $data = json_decode(file_get_contents('php://input'), true);

                $showQRCode = $data['showQRCode'];

                $tabParam['EASYURL_SHOW_QRCODE'] = $showQRCode;

                dol_set_user_param($this->db, $conf, $user, $tabParam);
            }
        }

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the digiqualiPublicControlTab function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function digiqualiPublicControlTab(array $parameters): int
    {
        global $langs;

        if (isModEnabled('digiquali') && $parameters['objectType'] == 'productlot') {
            $langs->load('easyurl@easyurl');

            $out  = '<div class="tab switch-public-control-view' . ($parameters['route'] == 'assignQRCode' ? ' tab-active' : '') . '" data-route="assignQRCode">';
            $out .= $langs->transnoentities('AssignQRCode');
            $out .= '</div>';
            $parameters['routes']['assignQRCode'] = '/../../../easyurl/public/frontend/assign_qrcode_view.tpl.php';
            $parameters['externals'][]            = 'assignQRCode';

            $this->resprints = $out;
        }

        return 0; // or return 1 to replace standard code
    }
}

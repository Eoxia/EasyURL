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
 * \file    public/frontend/assign_qr_code_view.tpl.php
 * \ingroup easyurl
 * \brief   Template to assign QR code from public interface
 */

/**
 * The following vars must be defined:
 * Global     : $db, $langs, $user
 * Parameters : $entity, $trackId (optional)
 * Variable   : $objectId, $linkableElement, $fromExternModule (optional)
 */

// Load EasyURL libraries
require_once __DIR__ . '/../../class/shortener.class.php';

// Initialize technical objects
$object = new Shortener($db);

$permissionToAssign = $user->hasRight('easyurl', 'shortener', 'assign');

print '<form id="public-shortener-form" method="POST" action="' . $_SERVER['PHP_SELF'] . (!empty($trackId) ? '?track_id=' .  $trackId . '&' : '?') . 'entity=' . $entity . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="assign_qrcode">'; ?>

    <div class="<?php echo (!empty($fromExternModule) ? '' : 'public-card__container')?>" data-public-interface="true">
        <?php if (getDolGlobalInt('SATURNE_ENABLE_PUBLIC_INTERFACE')) : ?>
            <div class="public-card__header">
                <div class="header-information">
                    <h1 class="information-title"><?php echo $langs->transnoentities('AssignQRCode'); ?></h1>
                </div>
            </div>
            <div class="public-card__content">
                <div class="wpeo-gridlayout grid-3">
                    <div>
                        <?php
                        if (!empty($linkableElement)) {
                            $linkableElementArrays  = [];
                            $linkableElementObjects = saturne_fetch_all_object_type($linkableElement['class_name'] ?? $linkableElement['className']);
                            if (is_array($linkableElementObjects) && !empty($linkableElementObjects)) {
                                foreach ($linkableElementObjects as $linkableElementObject) {
                                    $linkableElementArrays[$linkableElementObject->id] = $linkableElementObject->{$linkableElement['name_field']};
                                }
                            }
                            print Form::selectarray('fk_element', $linkableElementArrays, $objectId, $langs->transnoentities('NumProductLot'), 0, 0, '',  0, 0, !empty($trackId));
                        }
                        ?>
                    </div>

                    <div>
                        <?php
                        $shortenerArrays = [];
                        $shorteners      = $object->fetchAll('', '', 0, 0, ['customsql' => 't.status = ' . Shortener::STATUS_VALIDATED]);
                        if (is_array($shorteners) && !empty($shorteners)) {
                            foreach ($shorteners as $shortener) {
                                $shortenerArrays[$shortener->id] = $shortener->label;
                            }
                        }
                        print Form::selectarray('shortenerId', $shortenerArrays, '', $langs->transnoentities('NumQRCode'));
                        ?>
                    </div>
                    <?php if ($permissionToAssign) : ?>
                        <button type="submit" class="wpeo-button" style="background: var(--butactionbg); border-color: var(--butactionbg);"><?php echo $langs->transnoentities('Assign'); ?></button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else :
            print '<div class="center">' . $langs->trans('PublicInterfaceForbidden', $langs->transnoentities('OfAssignShortener')) . '</div>';
        endif; ?>
    </div>
<?php print '</form>';

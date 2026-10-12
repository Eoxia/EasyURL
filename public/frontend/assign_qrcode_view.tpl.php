<?php
/* Copyright (C) 2025-2026 EVARISK <technique@evarisk.com>
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
 * \file    public/frontend/assign_qrcode_view.tpl.php
 * \ingroup easyurl
 * \brief   Screen to assign a QR code to an object, in the PWA design of the public pages.
 *
 * Rendered by public/shortener/public_shortener.php and, as the assignQRCode view, by the public control page of
 * DigiQuali (inherits their scope). Expects: $db, $langs, $user, $entity, $linkedObject, $linkableElement and the
 * optional $trackId, $objectId, $fromExternModule.
 */

// Load EasyURL libraries
require_once __DIR__ . '/../../class/shortener.class.php';

// Initialize technical objects
$shortener = new Shortener($db);

$permissionToAssign = $user->hasRight('easyurl', 'shortener', 'assign');
$permissionToWrite  = $user->hasRight('easyurl', 'shortener', 'write');

// Opened from the public page of an object, the object is fixed: the other objects are not listed
$fixedObject = (!empty($objectId) && is_object($linkedObject) && $linkedObject->id > 0) ? $linkedObject : null;

// The DigiQuali page shows this screen through its route
$selfUrl = $_SERVER['PHP_SELF'] . '?' . http_build_query(array_filter([
    'track_id' => $trackId ?? '',
    'entity'   => $entity,
    'route'    => !empty($fromExternModule) ? 'assignQRCode' : ''
]));

$fixedObjectProduct = '';
$assignedShorteners = [];
if (!empty($fixedObject)) {
    if (!empty($fixedObject->fk_product)) {
        require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
        $product = new Product($db);
        if ($product->fetch($fixedObject->fk_product) > 0) {
            $fixedObjectProduct = $product->ref . ' - ' . $product->label;
        }
    }

    $assignedShorteners = $shortener->fetchAll('ASC', 't.label', 0, 0, ['customsql' => "t.element_type = '" . $db->escape($fixedObject->element) . "' AND t.fk_element = " . ((int) $fixedObject->id) . ' AND t.status = ' . Shortener::STATUS_ASSIGN]);
    $assignedShorteners = is_array($assignedShorteners) ? $assignedShorteners : [];
}

// The image of the QR codes replaces their icon for a user who asked for it, as in the object card
$showQRCode      = !empty($user->id) && getDolUserInt('EASYURL_SHOW_QRCODE');
$qrCodeImageUrls = [];
if ($showQRCode && !empty($assignedShorteners)) {
    require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
    foreach ($assignedShorteners as $assignedShortener) {
        $qrCodeDir   = 'shortener/' . $assignedShortener->ref . '/qrcode';
        $qrCodeFiles = dol_dir_list($conf->easyurl->multidir_output[$conf->entity] . '/' . $qrCodeDir, 'files', 0, '\.png$');
        if (!empty($qrCodeFiles)) {
            // Same image wrapper as the object card, which also serves the public pages
            $qrCodeImageUrls[$assignedShortener->id] = DOL_URL_ROOT . '/custom/saturne/utils/viewimage.php?modulepart=easyurl&entity=' . $conf->entity . '&file=' . urlencode($qrCodeDir . '/' . $qrCodeFiles[0]['name']);
        }
    }
}

// The clicks are read on the link of the object, as in the object card
$shortenerClicks = null;
if (!empty($assignedShorteners) && getDolGlobalInt('EASYURL_SHOW_API_INFOS')) {
    require_once __DIR__ . '/../../lib/easyurl_function.lib.php';
    $shortenerData   = get_easy_url_link($fixedObject, 'all');
    $shortenerClicks = (is_object($shortenerData) && isset($shortenerData->link->clicks)) ? (int) $shortenerData->link->clicks : 0;
}

// The lists are only loaded for a user who can assign
$availableShorteners = [];
$objectOptions       = [];
if ($permissionToAssign) {
    $shorteners = $shortener->fetchAll('ASC', 't.label', 0, 0, ['customsql' => 't.status = ' . Shortener::STATUS_VALIDATED]);
    if (is_array($shorteners)) {
        foreach ($shorteners as $availableShortener) {
            $availableShorteners[$availableShortener->id] = $availableShortener->label;
        }
    }

    if (empty($fixedObject) && !empty($linkableElement)) {
        $linkableObjects = saturne_fetch_all_object_type($linkableElement['class_name'] ?? $linkableElement['className']);
        if (is_array($linkableObjects)) {
            foreach ($linkableObjects as $linkableObject) {
                $objectOptions[$linkableObject->id] = $linkableObject->{$linkableElement['name_field']};
            }
        }
    }
} ?>

<div class="easyurl-assign">
    <?php if (!getDolGlobalInt('SATURNE_ENABLE_PUBLIC_INTERFACE')) : ?>
        <p class="easyurl-assign__notice">
            <i class="fas fa-lock"></i>
            <span><?php echo $langs->transnoentities('PublicInterfaceForbidden', $langs->transnoentities('OfAssignShortener')); ?></span>
        </p>
    <?php else :
        if (!empty($fixedObject)) : ?>
            <div class="easyurl-assign__object">
                <span class="easyurl-assign__icon"><?php echo img_picto('', $linkableElement['picto'] ?? 'generic'); ?></span>
                <div class="easyurl-assign__body">
                    <span class="easyurl-assign__label"><?php echo $langs->transnoentities('NumProductLot'); ?></span>
                    <span class="easyurl-assign__title"><?php echo dol_escape_htmltag($fixedObject->{$linkableElement['name_field']} ?? $fixedObject->ref); ?></span>
                    <?php if (!empty($fixedObjectProduct)) : ?>
                        <span class="easyurl-assign__muted"><?php echo dol_escape_htmltag($fixedObjectProduct); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="easyurl-assign__section-head">
                <h3 class="easyurl-assign__section-title"><i class="fas fa-qrcode"></i> <?php echo $langs->transnoentities('AssignedQRCodes'); ?></h3>
                <div class="easyurl-assign__section-actions">
                    <?php if (!empty($user->id)) : // Handled by shortener.js, which saves the choice of the user ?>
                        <span class="easyurl-assign__toggle show-qrcode" role="button" tabindex="0" title="<?php echo dol_escape_htmltag($langs->transnoentities('ShowQRCode')); ?>">
                            <?php echo img_picto('', $showQRCode ? 'switch_on' : 'switch_off'); ?>
                            <span><?php echo $langs->transnoentities('ShowQRCodeImage'); ?></span>
                        </span>
                    <?php endif;
                    if ($permissionToWrite) {
                        print dolButtonToOpenUrlInDialogPopup('assignShortener', $langs->transnoentities('AssignShortener'), '<i class="fas fa-link"></i>', '/custom/easyurl/view/shortener/shortener_card.php?element_type=' . $fixedObject->element . '&fk_element=' . $fixedObject->id . '&from_element=1&action=edit_assign', '', 'easyurl-assign__action', 'window.saturne.toolbox.checkIframeCreation();');
                    } ?>
                </div>
            </div>
            <?php if (!empty($assignedShorteners)) : ?>
                <div class="easyurl-assign__list">
                    <?php foreach ($assignedShorteners as $assignedShortener) :
                        $shortenerCardUrl = dol_buildpath('/easyurl/view/shortener/shortener_card.php', 1) . '?id=' . $assignedShortener->id . '&token=' . newToken() . '&backtopage=' . urlencode($selfUrl); ?>
                        <div class="easyurl-assign__item">
                            <div class="easyurl-assign__item-head">
                                <?php if (!empty($qrCodeImageUrls[$assignedShortener->id])) : ?>
                                    <img class="easyurl-assign__qrcode" src="<?php echo dol_escape_htmltag($qrCodeImageUrls[$assignedShortener->id]); ?>" alt="<?php echo dol_escape_htmltag($assignedShortener->label); ?>">
                                <?php else : ?>
                                    <span class="easyurl-assign__icon easyurl-assign__icon--qrcode"><i class="fas fa-qrcode"></i></span>
                                <?php endif; ?>
                                <div class="easyurl-assign__body">
                                    <span class="easyurl-assign__title"><?php echo dol_escape_htmltag($assignedShortener->label); ?></span>
                                    <?php if (!empty($assignedShortener->short_url)) : ?>
                                        <a class="easyurl-assign__link" href="<?php echo dol_escape_htmltag($assignedShortener->short_url); ?>" target="_blank" rel="noopener"><?php echo dol_escape_htmltag(preg_replace('/^https?:\/\//', '', $assignedShortener->short_url)); ?></a>
                                    <?php endif; ?>
                                </div>
                                <?php if ($permissionToWrite) : ?>
                                    <a class="easyurl-assign__action" href="<?php echo dol_escape_htmltag($shortenerCardUrl . '&element_type=' . $fixedObject->element . '&fk_element=' . $fixedObject->id . '&from_element=1&action=edit'); ?>" title="<?php echo $langs->transnoentities('Modify'); ?>">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a class="easyurl-assign__action easyurl-assign__action--danger easyurl-assign__unassign" href="<?php echo dol_escape_htmltag($shortenerCardUrl . '&action=unassign'); ?>" title="<?php echo $langs->transnoentities('Unassign'); ?>" data-confirm="<?php echo dol_escape_htmltag($langs->transnoentities('ConfirmUnassignQRCode', $assignedShortener->label)); ?>">
                                        <i class="fas fa-unlink"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="easyurl-assign__details">
                                <div class="easyurl-assign__detail">
                                    <span class="easyurl-assign__detail-label"><?php echo $langs->transnoentities('OriginalUrl'); ?></span>
                                    <?php if (!empty($assignedShortener->original_url)) : ?>
                                        <a class="easyurl-assign__detail-value easyurl-assign__link" href="<?php echo dol_escape_htmltag($assignedShortener->original_url); ?>" target="_blank" rel="noopener"><?php echo dol_escape_htmltag(preg_replace('/^https?:\/\//', '', $assignedShortener->original_url)); ?></a>
                                    <?php else : ?>
                                        <span class="easyurl-assign__detail-value">-</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($shortenerClicks !== null) : ?>
                                    <div class="easyurl-assign__detail">
                                        <span class="easyurl-assign__detail-label"><?php echo $langs->transnoentities('NbClick'); ?></span>
                                        <span class="easyurl-assign__detail-value"><?php echo $shortenerClicks; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="easyurl-assign__notice"><i class="fas fa-info-circle"></i> <span><?php echo $langs->transnoentities('NoQRCodeAssigned'); ?></span></p>
            <?php endif;
        endif; ?>

        <h3 class="easyurl-assign__section-title"><i class="fas fa-link"></i> <?php echo $langs->transnoentities('AssignNewQRCode'); ?></h3>
        <?php if (!$permissionToAssign) : ?>
            <p class="easyurl-assign__notice"><i class="fas fa-lock"></i> <span><?php echo $langs->transnoentities('AssignQRCodeNoPermission'); ?></span></p>
        <?php elseif (empty($availableShorteners)) : ?>
            <p class="easyurl-assign__notice"><i class="fas fa-info-circle"></i> <span><?php echo $langs->transnoentities('NoQRCodeAvailable'); ?></span></p>
        <?php else : ?>
            <form class="easyurl-assign__form" method="POST" action="<?php echo dol_escape_htmltag($selfUrl); ?>">
                <input type="hidden" name="token" value="<?php echo newToken(); ?>">
                <input type="hidden" name="action" value="assign_qrcode">
                <?php if (!empty($fixedObject)) : ?>
                    <input type="hidden" name="fk_element" value="<?php echo (int) $fixedObject->id; ?>">
                <?php else : ?>
                    <div class="easyurl-assign__field">
                        <label for="fk_element"><?php echo $langs->transnoentities('NumProductLot'); ?></label>
                        <?php print Form::selectarray('fk_element', $objectOptions, GETPOSTINT('fk_element'), $langs->transnoentities('NumProductLot'), 0, 0, '', 0, 0, 0, '', 'easyurl-assign__select'); ?>
                    </div>
                <?php endif; ?>

                <div class="easyurl-assign__field">
                    <label for="shortenerId"><?php echo $langs->transnoentities('NumQRCode'); ?></label>
                    <?php // Initialized by publicinterface.js, whose search also finds a QR code from the URL read on its label
                    print Form::selectarray('shortenerId', $availableShorteners, '', $langs->transnoentities('NumQRCode'), 0, 0, '', 0, 0, 0, '', 'easyurl-assign__select', 0); ?>
                    <span class="easyurl-assign__help"><?php echo $langs->transnoentities('AssignQRCodeSearchHelp'); ?></span>
                </div>

                <button type="submit" class="easyurl-assign__submit">
                    <i class="fas fa-link"></i> <?php echo $langs->transnoentities('Assign'); ?>
                </button>
            </form>
        <?php endif;
    endif; ?>
</div>

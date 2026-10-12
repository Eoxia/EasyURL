/* Copyright (C) 2025-2026 EVARISK <technique@evarisk.com>
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
 * \file    js/modules/publicinterface.js
 * \ingroup easyurl
 * \brief   JavaScript publicinterface file for module EasyURL
 */

'use strict';

/**
 * Init publicInterface JS
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @type {Object}
 */
window.easyurl.publicinterface = {};

/**
 * PublicInterface init
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   1.1.0
 * @version 23.2.0
 *
 * @returns {void}
 */
window.easyurl.publicinterface.init = function() {
  window.easyurl.publicinterface.event();

  $(document).ready(window.easyurl.publicinterface.initAssignForm);
};

/**
 * PublicInterface event
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   1.1.0
 * @version 23.2.0
 *
 * @returns {void}
 */
window.easyurl.publicinterface.event = function() {
  $(document).on('change', '.easyurl-assign__form select', window.easyurl.publicinterface.updateAssignButton);
  $(document).on('click', '.easyurl-assign__unassign', window.easyurl.publicinterface.confirmUnassign);
};

/**
 * Init the QR code search of the assign form
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   23.2.0
 * @version 23.2.0
 *
 * @returns {void}
 */
window.easyurl.publicinterface.initAssignForm = function() {
  const $form = $('.easyurl-assign__form');
  if (!$form.length) {
    return;
  }

  const $shortenerSelect = $form.find('#shortenerId');
  if ($shortenerSelect.length && typeof $.fn.select2 !== 'undefined') {
    $shortenerSelect.select2({
      width: '100%',
      // The empty choice of Form::selectarray has the value -1
      placeholder: { id: '-1', text: $shortenerSelect.find('option[value="-1"]').text() },
      language: typeof select2arrayoflanguage !== 'undefined' ? select2arrayoflanguage : undefined,
      matcher: window.easyurl.publicinterface.matchQRCode
    });
  }

  window.easyurl.publicinterface.updateAssignButton();
};

/**
 * Find a QR code from its number or from the URL read on its label
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   23.2.0
 * @version 23.2.0
 *
 * @param  {Object}      params Search parameters of select2
 * @param  {Object}      data   Option to match
 * @returns {Object|null}       The option when it matches, null otherwise
 */
window.easyurl.publicinterface.matchQRCode = function(params, data) {
  if ($.trim(params.term) === '') {
    return data;
  }

  // Of a pasted URL only its last part is the number of the QR code
  const term = params.term.replace(/^https?:\/\//, '').split('/').pop().toLowerCase().replace(/[-\s]/g, '');
  if (!term) {
    return null;
  }

  const normalizedText = data.text.toLowerCase().replace(/[-\s]/g, '');

  return normalizedText.includes(term) ? data : null;
};

/**
 * Enable the assign button once every choice of the form is made
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   23.2.0
 * @version 23.2.0
 *
 * @returns {void}
 */
window.easyurl.publicinterface.updateAssignButton = function() {
  const $form = $('.easyurl-assign__form');

  let isComplete = true;
  $form.find('select').each(function() {
    if (!(parseInt($(this).val()) > 0)) {
      isComplete = false;
    }
  });

  $form.find('.easyurl-assign__submit').prop('disabled', !isComplete);
};

/**
 * Ask a confirmation before unassigning a QR code
 *
 * @memberof EasyURL_PublicInterface
 *
 * @since   23.2.0
 * @version 23.2.0
 *
 * @param  {Event} event Click event
 * @returns {void}
 */
window.easyurl.publicinterface.confirmUnassign = function(event) {
  if (!window.confirm($(this).data('confirm'))) {
    event.preventDefault();
  }
};

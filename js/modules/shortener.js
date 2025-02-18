/* Copyright (C) 2021-2025 EVARISK <technique@evarisk.com>
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
 *
 * Library javascript to enable Browser notifications
 */

/**
 * \file    js/shortener.js
 * \ingroup easyurl
 * \brief   JavaScript shortener file for module EasyURL
 */

/**
 * Init shortener JS
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @type {Object}
 */
window.easyurl.shortener = {};

/**
 * Shortener init
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.shortener.init = function() {
  window.easyurl.shortener.event();
};

/**
 * Shortener event
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.shortener.event = function() {
  $(document).on('click', '.toggleObjectInfo', window.easyurl.shortener.toggleObjectInfo);
  $(document).on('click', '.show-qrcode', window.easyurl.shortener.showQRCode);
  $(document).on('change', '#fromid', window.easyurl.shortener.reloadAssignShortenerView);
  $(document).on('click', '.button-save.assign-button', window.easyurl.shortener.assignShortener);
  $(document).on('submit', '#generate-url-from', window.easyurl.shortener.buttonSave);
  $(document).on('change', '#element_type', window.saturne.utils.reloadField);
  $(document).on('keydown', '#generate-url-from', window.easyurl.shortener.keyDownForm);
};

/**
 * Show object info if toggle object info is on
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @return {void}
 */
window.easyurl.shortener.toggleObjectInfo = function () {
  if ($(this).hasClass('fa-minus-square')) {
    $(this).removeClass('fa-minus-square').addClass('fa-caret-square-down');
    $(this).closest('.fiche').find('.ObjectInfo tbody').hide();
  } else {
    $(this).removeClass('fa-caret-square-down').addClass('fa-minus-square');
    $(this).closest('.fiche').find('.ObjectInfo tbody').show();
  }
};

/**
 * Enables/disables the configuration to display QRCode instead of URL
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @return {void}
 */
window.easyurl.shortener.showQRCode = function() {
  let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);
  let token          = window.saturne.toolbox.getToken();

  let showQRCode;
  if ($(this).hasClass('fa-toggle-off')) {
    showQRCode = 1;
  } else {
    showQRCode = 0;
  }

  window.saturne.loader.display($(this));

  $.ajax({
    url: document.URL + querySeparator + "action=show_qrcode&token=" + token,
    type: "POST",
    processData: false,
    data: JSON.stringify({
      showQRCode: showQRCode
    }),
    contentType: false,
    success: function(resp) {
      window.location.reload();
    },
    error: function() {}
  });
};

/**
 * Reload short_url field and assign shortener view
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.shortener.reloadAssignShortenerView = function() {
  let field          = $(this).val();
  let token          = window.saturne.toolbox.getToken();
  let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

  window.saturne.loader.display($('.assign-form'));

  $.ajax({
    url: document.URL + querySeparator + "fromid=" + field + "&token=" + token,
    type: "POST",
    processData: false,
    contentType: false,
    success: function(resp) {
      $('.assign-form').replaceWith($(resp).find('.assign-form'));
    },
    error: function() {}
  });
};

/**
 * Assign shortener
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.shortener.assignShortener = function() {
  let token          = window.saturne.toolbox.getToken();
  let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

  $.ajax({
    url: document.URL + querySeparator + "view&token=" + token,
    type: "POST",
    processData: false,
    contentType: false,
    success: function() {
      window.parent.jQuery('#idfordialogassignShortener').dialog('close');
    },
    error: function() {}
  });
};

/**
 * ExportShortener button save
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.shortener.buttonSave = function(e) {
  e.preventDefault();
  let form = new FormData($('#generate-url-from')[0]);
  let nbUrl = parseInt(form.get('nb_url'));

  window.saturne.loader.display($('#generate-url-from .button-save'));
  window.easyurl.shortener.createShortener(form, 1, nbUrl);
}

/**
 * ExportShortener create link
 *
 * @memberof EasyURL_Shortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.shortener.createShortener = function(form, current, nbUrl) {
  let token = window.saturne.toolbox.getToken();
  let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

  $.ajax({
    method: 'POST',
    url: document.URL + querySeparator + 'action=generate_url&nb_url=' + current + '&token=' + token,
    data: JSON.stringify(Object.fromEntries(form)),
    processData: false,
    contentType: false,
    success: function (resp) {
      let success = $(resp).find('input[name="success"]').val();

      if (success == 'true') {
        if (current < nbUrl) {
          window.saturne.notice.showNotice('notice-infos', 'ExportGenerating', 'ExportGenerating ' + current + ' URLs ! <div class="wpeo-loader" style="display: inline; margin-left: 2rem;"><span class="loader-spin wpeo-loader"></span></div>', 'warning');
          window.easyurl.shortener.createShortener(form, ++current, nbUrl);
        } else {
          window.easyurl.exportshortener.generateExport(nbUrl);
        }
      } else {
        window.saturne.notice.showNotice('notice-infos', 'Error', 'ExportError ' + current, 'error');
      }
    },
    error: function() {
      window.saturne.notice.showNotice('notice-infos', 'Error', 'ExportError ' + current, 'error');
    }
  });
};

/**
 * ExportShortener key down form
 *
 * @since  1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */

window.easyurl.shortener.keyDownForm = function(e) {
  if (e.key == 'Enter' && e.target.tagName == 'INPUT') {
    e.preventDefault();
  }
}

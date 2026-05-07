/* Copyright (C) 2024-2024 EVARISK <technique@evarisk.com>
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
 * \file    js/exportshortener.js
 * \ingroup easyurl
 * \brief   JavaScript exportshortener file for module EasyURL
 */

'use strict';

/**
 * Init exportshortener JS
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @type {Object}
 */
window.easyurl.exportshortener = {};

/**
 * ExportShortener init
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.init = function() {
  window.easyurl.exportshortener.event();
};

/**
 * ExportShortener event
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.event = function() {
};

/**
 * ExportShortener generate export
 *
 * @memberof EasyURL_ExportShortener
 *
 * @since   1.1.0
 * @version 1.1.0
 *
 * @returns {void}
 */
window.easyurl.exportshortener.generateExport = function(nbUrl) {
  let token          = window.saturne.toolbox.getToken();
  let querySeparator = window.saturne.toolbox.getQuerySeparator(document.URL);

  $.ajax({
    method: 'POST',
    url: document.URL + querySeparator + 'action=generate_export&nb_url=' + nbUrl + '&token=' + token,
    processData: false,
    contentType: false,
    success: function (resp) {
      window.saturne.notice.showNotice('notice-infos', 'Success', 'YouGenerated ' + nbUrl + ' UrlWithSuccess', 'success');
      window.saturne.loader.remove($('#generate-url-from .button-save'));
      $('#shortener-export-table').replaceWith($(resp).find('#shortener-export-table'));
    },
    error: function() {
      window.saturne.notice.showNotice('notice-infos', 'Error', 'ExportError', 'error');
      window.saturne.loader.remove($('#generate-url-from .button-save'));
    }
  });
};

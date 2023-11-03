<?php
/* Copyright (C) 2021-2023 EVARISK <technique@evarisk.com>
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
 * \file    lib/easyurl_shortener.lib.php
 * \ingroup easyurl
 * \brief   Library files with common functions for Shortener
 */

// Load Saturne libraries
require_once __DIR__ . '/../../saturne/lib/object.lib.php';

/**
 * Prepare shortener pages header
 *
 * @param  Shortener $object Shortener
 * @return array     $head   Array of tabs
 * @throws Exception
 */
function shortener_prepare_head(Shortener $object): array
{
    return saturne_object_prepare_head($object, [], [], false, false, false);
}

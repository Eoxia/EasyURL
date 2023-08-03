<?php
/* Copyright (C) 2023 EVARISK <technique@evarisk.com>
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
 * or see https://www.gnu.org/
 */

/**
 * \file    core/substitutions/functions_tinyurl.lib.php
 * \ingroup functions_tinyurl
 * \brief   File of functions to substitutions array
 */

/** Function called to complete substitution array (before generating on ODT, or a personalized email)
 * functions xxx_completesubstitutionarray are called by make_substitutions() if file
 * is inside directory htdocs/core/substitutions
 *
 * @param  array     $substitutionarray Array with substitution key => val
 * @param  Translate $langs             Output langs
 * @param  Object    $object            Object to use to get values
 * @return void                         The entry parameter $substitutionarray is modified
 */
function tinyurl_completesubstitutionarray(&$substitutionarray, $langs, $object)
{
    switch ($object->element) {
        case 'propal':
        case 'commande':
        case 'facture':
            $substitutionarray['__TINY_URL_LINK__'] =  $object->array_options['options_tiny_url_link'];
            break;
    }
}

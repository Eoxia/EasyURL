# [EasyURL] [23.0.0] - Interface publique de raccourcis - Gestion QRCode - API enrichie

Description : Cette version majeure introduit une interface publique pour les raccourcis et QRCodes (auto-assignation, configuration externe, hooks d'intégration), enrichit la classe Shortener avec un suivi détaillé des clics et fournit une refonte complète de la liaison externe avec d'autres modules (DigiQuali, etc.). Saut de version 1.0.0 → 23.0.0 pour aligner sur la famille Saturne / Dolibarr 23.

## Nouvelles fonctionnalités et innovations

### Interface publique des raccourcis et QRCodes

* Nouvelle **page publique de raccourcis** (Shortener) avec configuration dédiée et CSS associé.
* Action `assign_qrcode` côté public : permet de scanner un QRCode et de l'assigner directement à un élément.
* Liste publique de QRCodes avec gestion des permissions (vue conditionnelle selon les droits).
* Hook ouvert pour les modules tiers (utilisé par DigiQuali) afin d'étendre l'interface publique.
* Désactivation conditionnelle du champ `fk_element` lorsqu'un `trackId` est déjà présent.
* `linkableElement` ID par défaut configurable.
* Intégration avec un module externe pour piloter l'interface publique.

<!-- 📸 Ajouter une screenshot ici -->

### Suivi des clics et statistiques

* Nouvelle fonction `get_click_date` dans la lib EasyURL pour récupérer la date du clic.
* Refactor de l'appel cURL via une fonction dédiée `init_easy_url_curl` (mutualise les optimisations).
* `set_easy_url_link` retourne désormais des informations d'erreur exploitables.
* Champ « Date de modification » exposé dans la liste Shortener.

<!-- 📸 Ajouter une screenshot ici -->

### Configuration `EASYURL_DEFAULT_ORIGINAL_URL`

* Nouvelle constante de conf : URL par défaut pré-remplie dans le champ d'entrée pour faciliter la création.

---

## Améliorations & corrections

### Carte Shortener (admin)

* Bouton **« Désassigner »** sur la fiche Shortener avec ajout d'une URL de redirection.
* Action de désassignation enrichie via un trigger qui journalise les infos précédentes.
* Colspan du tableau corrigé.
* `showInputField` ajouté pour contourner un bug Dolibarr 20.
* Typo dans un libellé de statut corrigée.

### JS public et select2

* `select2` : URL correctement résolue dans la `ref`.
* JS public conformé à PSR-12.
* Garde « JS not ready » corrigée.
* Bouton « Exporter » qui restait grisé en cas d'erreur — corrigé.
* Création d'export possible via la touche **Entrée** dans le champ.
* Action `submit` remplacée par `click` pour fiabiliser, et JSON null géré correctement.

### Affichage public

* Toggle inutile retiré pour les utilisateurs non connectés.
* Placeholder de la vue QRCode publique corrigé.
* Type d'URL et impression de l'URL courte améliorés.
* Inclusion CSS multiple supprimée du hook.

### Permissions / vérifications

* Vérification améliorée sur la création de raccourci public.
* Vérification du bon objet sur la page publique.
* Permissions de visualisation de la liste Shortener publique gérées.
* Permission pour assigner ajoutée.

### Module / paramètres

* Paramètres et documentation manquants ajoutés sur Shortener.
* `saturne_get_objects_metadata` correctement géré dans `showInputField`.
* Plusieurs passes de nettoyage de code (`[Class] core: clean code`).
* Améliorations sur `setEventMessages` (`AssignQRCodeSuccess`).

### Traductions

* Correction d'une erreur d'orthographe (#43).
* Trad changée sans backward compatibility (à surveiller en migration).

## Comparaison des versions [1.0.0](https://github.com/Eoxia/EasyURL/compare/1.0.0...23.0.0) et 23.0.0

* [#107] [Shortener] feat: public shortener cleanup, QRCode list, perm view [`8d3c18f`](https://github.com/Eoxia/EasyURL/commit/8d3c18f) [`b6cabd3`](https://github.com/Eoxia/EasyURL/commit/b6cabd3) [`681d984`](https://github.com/Eoxia/EasyURL/commit/681d984) [`ac480f5`](https://github.com/Eoxia/EasyURL/commit/ac480f5)
* [#108] [PublicQRcodeView] fix: placeholder not working [`44f402f`](https://github.com/Eoxia/EasyURL/commit/44f402f)
* [#105] [PublicShortener] add: external module management, fk_element guard [`095943e`](https://github.com/Eoxia/EasyURL/commit/095943e) [`98941fa`](https://github.com/Eoxia/EasyURL/commit/98941fa)
* [#103] [Trad] fix: change trad but dont have backward on yet [`0f82441`](https://github.com/Eoxia/EasyURL/commit/0f82441)
* [#100] [ExportShortenerJS] fix: button stay gray when error [`4a0ead4`](https://github.com/Eoxia/EasyURL/commit/4a0ead4)
* [#99] [ShortenerJS] fix: enter to export, click vs submit, json null [`149f2bc`](https://github.com/Eoxia/EasyURL/commit/149f2bc) [`be7461a`](https://github.com/Eoxia/EasyURL/commit/be7461a) [`095943e`](https://github.com/Eoxia/EasyURL/commit/095943e)
* [#95] [PublicShortener] add: default linkableElement ID [`d36eb6d`](https://github.com/Eoxia/EasyURL/commit/d36eb6d)
* [#94] [PublicShortener/JS] fix: improve check, target [`5a86cc3`](https://github.com/Eoxia/EasyURL/commit/5a86cc3) [`212fd56`](https://github.com/Eoxia/EasyURL/commit/212fd56)
* [#92] [ShortenerClass] add: modification date in list [`0bdee9d`](https://github.com/Eoxia/EasyURL/commit/0bdee9d)
* [#88] [ShortenerCard] fix: typo in status [`0957fd7`](https://github.com/Eoxia/EasyURL/commit/0957fd7)
* [#86] [Shortener] fix: showInputField improve for saturne_get_objects_metadata [`2499d62`](https://github.com/Eoxia/EasyURL/commit/2499d62)
* [#84] [Shortener/Card] fix/add: unassign action with trigger info, button to unassign, redirect URL [`52cd294`](https://github.com/Eoxia/EasyURL/commit/52cd294) [`5c1a7a3`](https://github.com/Eoxia/EasyURL/commit/5c1a7a3) [`29ec45e`](https://github.com/Eoxia/EasyURL/commit/29ec45e)
* [#80] [PublicShortener/Tools] add: public shortener config, CSS, page, perm to assign, hook for digiquali, EASYURL_DEFAULT_ORIGINAL_URL [`f38d603`](https://github.com/Eoxia/EasyURL/commit/f38d603) [`a229e61`](https://github.com/Eoxia/EasyURL/commit/a229e61) [`a5509f5`](https://github.com/Eoxia/EasyURL/commit/a5509f5) [`389a20c`](https://github.com/Eoxia/EasyURL/commit/389a20c) [`05e0eec`](https://github.com/Eoxia/EasyURL/commit/05e0eec)
* [#71] [Shortener] add: showInputField for Dolibarr 20 bug, missing parameters/doc [`3c4e03b`](https://github.com/Eoxia/EasyURL/commit/3c4e03b) [`c24bf13`](https://github.com/Eoxia/EasyURL/commit/c24bf13)
* [#61] [Lib/Shortener] add: get click date, init_easy_url_curl, error info, manage colspan [`7707f0d`](https://github.com/Eoxia/EasyURL/commit/7707f0d) [`4920dc2`](https://github.com/Eoxia/EasyURL/commit/4920dc2) [`b1ab689`](https://github.com/Eoxia/EasyURL/commit/b1ab689) [`2ff6439`](https://github.com/Eoxia/EasyURL/commit/2ff6439)
* [#43] [Trad] fix: spelling error [`225b8d5`](https://github.com/Eoxia/EasyURL/commit/225b8d5)
* [#1975] [PublicInterfaceJs] fix: PSR12, js ready, select2 url [`0f3c8ef`](https://github.com/Eoxia/EasyURL/commit/0f3c8ef) [`490f6cf`](https://github.com/Eoxia/EasyURL/commit/490f6cf) [`32e2592`](https://github.com/Eoxia/EasyURL/commit/32e2592)
* [Class] core: clean code (3 commits) [`dfc279a`](https://github.com/Eoxia/EasyURL/commit/dfc279a) [`071948c`](https://github.com/Eoxia/EasyURL/commit/071948c) [`3fb917f`](https://github.com/Eoxia/EasyURL/commit/3fb917f)

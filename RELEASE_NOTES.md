# [EasyURL] [23.3.0] - Affectation des QR codes repensée - Mot-clé sans préfixe de référence - Thème moderne

Description : L'affectation d'un QR code à un lot passe au design de l'application mobile de DigiQuali : le lot, ses QR codes et le formulaire tiennent sur un écran de téléphone. Une nouvelle option raccourcit le mot-clé des URL courtes YOURLS, et les écrans d'EasyURL adoptent le thème moderne des modules Evarisk.

**Cette version demande Saturne 23.8.0 ou supérieur.**

## Nouvelles fonctionnalités et innovations

### Affectation des QR codes repensée

* L'écran d'affectation d'un QR code prend le design de l'application mobile de DigiQuali, dans l'onglet **QR code** de la page publique d'un lot comme sur la page publique d'affectation d'EasyURL.
* En tête, **la carte du lot** et son produit. Dessous, **les QR codes affectés** : numéro, URL courte, URL d'origine, nombre de clics (avec l'option « Afficher les informations supplémentaires de l'API »), image du QR code à afficher d'un interrupteur, boutons **Modifier** et **Désaffecter**. La désaffectation demande maintenant une confirmation.
* Dans l'onglet QR code comme sur la page publique d'affectation, **tapez le numéro d'un QR code ou collez l'URL lue sur son étiquette** : il est retrouvé, en méthode YOURLS comme en méthode Saturne, dont le code suit le « ? » de l'adresse. Cette recherche ne s'appliquait jamais jusqu'ici. Le bouton **Affecter** ne s'active qu'une fois le choix fait.
* Sans le droit d'affecter, ou sans QR code disponible, un message remplace le formulaire. L'onglet s'appelle désormais « QR code », pour tenir sur une ligne dans la barre du bas.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.3.0/affectation-qr-code.png" width="32%" alt="Onglet QR code de la page publique d'un lot sur téléphone : carte du lot, QR code affecté avec ses boutons Modifier et Désaffecter, formulaire Affecter un QR code"> <img src="https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.3.0/recherche-url-etiquette.png" width="32%" alt="Page publique d'affectation sur téléphone : l'URL collée https://1url.re/dq2409042 retrouve le QR code dq2409042, le bouton Affecter reste grisé tant que le choix n'est pas fait">

### Mot-clé des URL courtes sans préfixe de référence

* Nouvelle option dans **Réglages** : « Mot-clé sans préfixe de référence », désactivée par défaut.
* Le mot-clé par défaut d'une URL courte YOURLS est le nom de votre société suivi de la référence de l'URL courte : `monentreprise-eu2611-0001`. Option activée, le préfixe de numérotation disparaît : `monentreprise-2611-0001`. L'adresse imprimée sur l'étiquette raccourcit d'autant.
* Le préfixe est lu dans le modèle de numérotation actif, pas écrit en dur, et n'est retiré que s'il ouvre la référence.
* Seules les URL courtes générées depuis les outils d'EasyURL sont concernées : les liens de paiement et de signature des propositions, commandes et factures gardent leur préfixe, qui distingue deux références de même numéro. En méthode Saturne, le code reste tiré au hasard.
* Un mot-clé saisi à la main n'est pas modifié, l'option SHA s'ajoute toujours derrière, et les URL courtes déjà créées ne changent pas.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.3.0/option-mot-cle.png" alt="Réglages d'EasyURL : l'option Mot-clé sans préfixe de référence activée, entre SHA dans l'URL raccourcie et Afficher les informations supplémentaires de l'API">

### Design moderne et administration

* Les écrans d'EasyURL adoptent le **thème moderne** des modules Evarisk, partagé par Saturne : accueil, listes, fiches, outils et configuration.
* La **console de génération** des outils garde son allure de terminal sombre, redessinée aux couleurs du thème. Sur l'onglet **Interface publique**, le lien de la page d'affectation et son bouton copier forment une carte.
* EasyURL **refuse de s'activer sur un Saturne plus ancien que 23.8.0**, au lieu d'une erreur fatale : le message donne la version attendue et la version installée.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.3.0/outils-console.png" alt="Outils d'EasyURL au thème moderne : génération de 3 URL courtes en méthode Saturne, et la console qui affiche chaque URL créée">

## Améliorations & corrections

### Affectation des QR codes

* Ouvert d'un clic depuis la page publique d'un lot, l'onglet QR code de DigiQuali n'initialisait pas son formulaire : pas de recherche, et un bouton Affecter actif sans aucun choix. Il est prêt dès son affichage.
* Les messages d'erreur d'affectation sont traduits : « AssignQRCodeErrors » s'affichait en clair.
* Sur la page d'un lot, la liste de tous les lots de l'entité n'est plus envoyée dans la page, y compris à un visiteur non connecté.
* Avec la protection anti-CSRF renforcée de Dolibarr (`MAIN_SECURITY_CSRF_WITH_TOKEN` à 3), la fenêtre d'affectation d'un lien court et la désaffectation finissaient en erreur 403 « Token not provided » : les liens portent maintenant le jeton.
* Après une mise à jour d'EasyURL, la page publique d'un lot recharge ses styles et ses scripts au lieu de garder l'ancienne version en cache.

### Traduction anglaise

* Hors français, les écrans d'EasyURL affichaient des clés brutes (QRCodeTab, NumProductLot…), jusque dans l'onglet QR code de la page publique d'un lot, ouvert par la personne qui scanne l'étiquette : EasyURL est désormais traduit en anglais.

### Fiches et listes

* Sur une fiche sans lien court, par exemple une commande sans lien de signature, un avertissement PHP s'affichait en bas de page et le service YOURLS recevait une demande de statistiques pour une adresse vide.
* Sur les pages qui appellent EasyURL sans objet, comme la liste des accidents de Digirisk, des dizaines d'avertissements PHP s'affichaient en haut de page, suivis de « headers already sent ».

### Stabilité et compatibilité

* Sans Saturne, la liste des modules de Dolibarr finissait en erreur fatale : elle s'affiche, et l'activation d'EasyURL est refusée avec un message.
* Pendant une réinitialisation de Saturne, une requête simultanée (un appel d'API par exemple) pouvait finir en erreur fatale `Call to undefined function saturne_load_langs()`.
* Installé par lien symbolique dans `htdocs/custom`, EasyURL cherchait Saturne au mauvais endroit et finissait en erreur fatale.
* EasyURL fonctionne dans une racine alternative à `custom/` : fenêtre d'affectation d'un lien sur les fiches lot et produit et sur l'historique public de DigiQuali, lien vers la page publique d'affectation, retour à la configuration depuis les outils, adresse de l'historique public DigiQuali enregistrée comme URL d'origine, bouton « Logs » de la console et image du QR code de la page publique.

## Testez les nouveautés maintenant

**Allez tester la nouvelle affectation des QR codes sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.2.0](https://github.com/Eoxia/EasyURL/compare/23.2.0...23.3.0) et 23.3.0

* [#222] [PublicInterface] fix: formulaire d'affectation initialisé quand l'onglet QR code DigiQuali est chargé en AJAX [`41ae8aa5`](https://github.com/Eoxia/EasyURL/commit/41ae8aa5)
* [#219] [JS] fix: bouton Logs de la console sans /custom/easyurl/ dans l'URL [`a13a926c`](https://github.com/Eoxia/EasyURL/commit/a13a926c)
* [#219] [Module] fix: popup d'affectation ouverte dans la racine du module [`4786109a`](https://github.com/Eoxia/EasyURL/commit/4786109a)
* [#219] [Module] fix: dol_buildpath('custom/…') sans custom/ [`86e663be`](https://github.com/Eoxia/EasyURL/commit/86e663be)
* [#219] [Module] fix: URL construites avec DOL_URL_ROOT . '/custom/…' [`67e2da4e`](https://github.com/Eoxia/EasyURL/commit/67e2da4e)
* [#217] [CI] ci: actions GitHub en Node 24 (checkout@v7, cache@v6) [`b0668ab6`](https://github.com/Eoxia/EasyURL/commit/b0668ab6)
* [#215] [CI] ci: contrôle « API Saturne publiée » dans la CI qualité [`8ced7553`](https://github.com/Eoxia/EasyURL/commit/8ced7553)
* [#213] [CI] ci: retrait du workflow Generate Auto Changelog [`cd263323`](https://github.com/Eoxia/EasyURL/commit/cd263323)
* [#211] [Mod] feat: version minimale de Saturne, activation refusee sur un Saturne trop ancien [`6cb05a16`](https://github.com/Eoxia/EasyURL/commit/6cb05a16)
* [#209] [Lang] fix: fichier en_US, plus de clés brutes hors français [`eb7fd7ed`](https://github.com/Eoxia/EasyURL/commit/eb7fd7ed)
* [#171] [JS] fix: la recherche par URL trouve aussi les QR codes Saturne [`8f1eb866`](https://github.com/Eoxia/EasyURL/commit/8f1eb866)
* [#207] [Trigger] fix: charger la lib de Saturne avant saturne_load_langs() [`4496de14`](https://github.com/Eoxia/EasyURL/commit/4496de14)
* [#205] [Shortener] feat: option mot-clé sans préfixe de référence [`b5ce7120`](https://github.com/Eoxia/EasyURL/commit/b5ce7120)
* [#203] [CI] ci: refuser les inclusions relatives de Saturne [`55120336`](https://github.com/Eoxia/EasyURL/commit/55120336)
* [#203] [Saturne] fix: inclure Saturne par dol_include_once / dol_buildpath [`adbef8b3`](https://github.com/Eoxia/EasyURL/commit/adbef8b3)
* [#199] [Release] docs: vidéo de la 23.2.0 sur YouTube dans RELEASE_NOTES.md [`09eb94d7`](https://github.com/Eoxia/EasyURL/commit/09eb94d7)
* [#199] [Release] docs: vidéo de la 23.2.0 sur YouTube dans RELEASE_NOTES.md [`930f8c3b`](https://github.com/Eoxia/EasyURL/commit/930f8c3b)
* [#197] [Module] fix: no fatal on admin/modules.php nor on activation when Saturne is missing [`b8d39565`](https://github.com/Eoxia/EasyURL/commit/b8d39565)
* [#195] [Hooks] fix: guard doActions against a missing object [`e29daec3`](https://github.com/Eoxia/EasyURL/commit/e29daec3)
* [#193] [Lib] fix: get_easy_url_link() ne lit plus un lien court absent [`54b36aac`](https://github.com/Eoxia/EasyURL/commit/54b36aac)
* [#190] [SCSS] feat: thème moderne des écrans EasyURL [`11a30631`](https://github.com/Eoxia/EasyURL/commit/11a30631)
* [#190] [Front] rework: style de la console des outils déplacé en SCSS [`e2e606dd`](https://github.com/Eoxia/EasyURL/commit/e2e606dd)
* [#176] [Hook] fix: doActions ne lit plus l'élément d'un objet absent [`a3dbfdcf`](https://github.com/Eoxia/EasyURL/commit/a3dbfdcf)
* [#175] [CSRF] fix: ajouter le token CSRF aux liens et appels AJAX [`bea81b0c`](https://github.com/Eoxia/EasyURL/commit/bea81b0c)
* [#171] [CI] chore: build assets [`e7e95399`](https://github.com/Eoxia/EasyURL/commit/e7e95399)
* [#171] [PublicShortener] rework: écran d'affectation de QR code au design de la PWA [`cb2124ba`](https://github.com/Eoxia/EasyURL/commit/cb2124ba)

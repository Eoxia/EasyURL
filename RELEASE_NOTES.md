# [EasyURL] [23.3.4] - QR codes sans avertissement quand le type lié est inconnu

Description : un QR code lié à un objet dont le type n'est pas connu de l'entité (par exemple un lot quand le module Lots est désactivé) s'affiche, se modifie et s'affecte sans aucun avertissement.

**Cette version demande Saturne 23.10.1 ou supérieur.**

## Améliorations & corrections

### QR codes liés à un type inconnu de l'entité

* La fiche du QR code (affichage, modification, création) et la liste des QR codes n'affichent plus d'avertissement : l'élément lié apparaît par son identifiant, au lieu de « Error bad setup of extrafield ».
* L'onglet QR code de la page publique d'un lot nomme le lot par son numéro.
* Le message d'affectation indique « … sur N° du lot/série <numéro> » au lieu de « ErrorBadValueForParamNotAString ».

## Testez les nouveautés maintenant

**Allez tester l'affectation d'un QR code sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.3.3](https://github.com/Eoxia/EasyURL/compare/23.3.3...23.3.4) et 23.3.4

* [#270] [Shortener] fix: aucun avertissement quand le type de l'objet lié n'est pas connu dans l'entité [`852561f0`](https://github.com/Eoxia/EasyURL/commit/852561f0)

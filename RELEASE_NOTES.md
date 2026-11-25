# [EasyURL] [23.3.3] - QR codes plus robustes sur les pages publiques

Description : la préférence d'affichage du QR code ne s'enregistre plus que pour un utilisateur connecté, la modification d'un QR code ne s'arrête plus sur une erreur quand le type de l'objet lié est inconnu de l'entité, et l'absence de Saturne est signalée clairement. **Mise à jour recommandée pour toutes les instances.**

**Cette version demande Saturne 23.10.1 ou supérieur.**

## Améliorations & corrections

### QR codes sur les pages publiques

* La préférence « afficher le QR code » ne s'enregistre plus que pour un utilisateur connecté, et seulement avec les valeurs prévues.
* Affecter ou modifier un QR code dont l'objet lié est d'un type inconnu de l'entité (par exemple un lot quand le module Lots est désactivé) ne provoque plus d'erreur fatale : l'affectation est enregistrée, le lien vers l'objet est simplement laissé de côté.

### Installation

* Quand Saturne est introuvable, le message d’erreur dit où l’installer : à côté d’EasyURL, dans le même dossier de modules.

## Testez les nouveautés maintenant

**Allez tester l'affectation d'un QR code sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.3.2](https://github.com/Eoxia/EasyURL/compare/23.3.2...23.3.3) et 23.3.3

* [#264] [Trigger] fix: QR code modifié sans erreur fatale quand le type de l'objet lié n'est pas connu dans l'entité [`9df99ba6`](https://github.com/Eoxia/EasyURL/commit/9df99ba6)
* [Hooks] fix: préférence d'affichage du QR code alignée sur la page publique d'EasyURL [`b5219462`](https://github.com/Eoxia/EasyURL/commit/b5219462)
* [#262] [Saturne] fix: message explicite quand l'amorçage ne trouve pas Saturne [`395f9311`](https://github.com/Eoxia/EasyURL/commit/395f9311)

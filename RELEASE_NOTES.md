# [EasyURL] [23.3.2] - Affectation des liens courts plus sûre - Configuration sans avertissement

Description : la page d'affectation d'un lien court contrôle l'entité de son lien, et la configuration du module ne laisse plus d'avertissements PHP. **Mise à jour recommandée pour toutes les instances.**

**Cette version demande Saturne 23.10.0 ou supérieur.**

## Améliorations & corrections

### Affectation d'un lien court : contrôles renforcés

* L'entité du lien est contrôlée avant toute lecture de la configuration.

### Configuration

* Les trois champs texte de la configuration n’ajoutent plus de balise `</td>` en double.
* Les champs de la configuration s'affichent sans avertissement quand leur valeur n'a jamais été enregistrée.

### Droits

* Les droits du module se lisent sans avertissement pour un utilisateur qui ne les a pas.

### Cohabitation avec les autres modules

* Un module qui déclenche un événement sur un objet qui n'est pas un objet Dolibarr ne laisse plus d'avertissements dans le journal PHP.

## Testez les nouveautés maintenant

**Allez tester l'affectation d'un QR code sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.3.1](https://github.com/Eoxia/EasyURL/compare/23.3.1...23.3.2) et 23.3.2

* [Public] fix: entité du lien lue comme un nombre [`f3756962`](https://github.com/Eoxia/EasyURL/commit/f3756962)
* [#255] [Admin] fix: plus de « </td> » en double après les trois champs texte de la configuration [`b6142175`](https://github.com/Eoxia/EasyURL/commit/b6142175)
* [#254] [Trigger] fix: plus d'avertissement sur un événement dont l'objet n'est pas un CommonObject [`f69b66e0`](https://github.com/Eoxia/EasyURL/commit/f69b66e0)
* [#251] [Rights] fix: droits lus par hasRight(), sans avertissement pour un utilisateur qui ne les a pas [`02d2b5ed`](https://github.com/Eoxia/EasyURL/commit/02d2b5ed)
* [#250] [Admin] fix: champs de la configuration lus par getDolGlobalString(), sans avertissement quand la constante n'existe pas [`ec52e471`](https://github.com/Eoxia/EasyURL/commit/ec52e471)

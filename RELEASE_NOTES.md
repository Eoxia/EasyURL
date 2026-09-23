# [EasyURL] [23.1.0] - Console de génération en temps réel - Édition en ligne du libellé

Description : Cette version dote la génération de masse d'une **console en temps réel**, persistante dans la vue, avec l'historique des erreurs, des compteurs OK/KO qui servent de filtres et un choix du nombre de lignes affichées. Le **libellé d'un raccourci s'édite directement dans la liste**. Elle remonte aussi les vraies erreurs de YOURLS au lieu de les masquer, et corrige les avertissements PHP 8 relevés par le premier passage du smoke test.

**Cette version demande Saturne 23.2.0 ou supérieur.**

## Nouvelles fonctionnalités et innovations

### Console de génération

* **Console en temps réel** pour la génération de masse, disponible en permanence dans la vue.
* Les 200 dernières erreurs sont chargées à l'ouverture de la page, et le nombre de lignes affichées se choisit dans l'en-tête.
* Les compteurs OK et KO servent de **filtres** : un clic masque ou réaffiche les lignes correspondantes.

### Liste des raccourcis

* Le **libellé s'édite en ligne**, directement dans la liste.

### Interface publique

* Icône sur l'onglet QR code de la navigation PWA du contrôle public DigiQuali.

## Améliorations & corrections

### Erreurs remontées

* `set_easy_url_link()` rend un entier et **signale les échecs cURL** au lieu de les avaler.
* La vraie erreur de YOURLS s'affiche à l'affectation d'un QR code.
* Plus d'avertissement sur le code de statut quand l'API renvoie un entier d'erreur.

### Accueil et outils

* La configuration de tableau de bord d'un utilisateur qui n'en a jamais posé est lue avec un défaut.
* Le compteur par type d'élément n'incrémente plus une clé absente.
* Un type d'élément **sans métadonnée** — module désactivé, type disparu — reçoit un libellé de repli : le graphe de répartition recevait plus de séries que de couleurs, et le cœur lisait un index absent.
* Le sélecteur de nombre de lignes et le compteur de KO de la console étaient câblés à des variables qui n'existaient pas : le choix était ignoré et la liste restait figée.
* Le paramètre utilisateur d'affichage du QR code est protégé.
* L'objet est vérifié avant la lecture de sa propriété `element`.

### Intégration continue

* Les assets sont compilés par la chaîne du socle, vérifiés à chaque poussée et sur les pull requests ; le gulpfile local est supprimé.

## Comparaison des versions [23.0.0](https://github.com/Eoxia/EasyURL/compare/23.0.0...23.1.0) et 23.1.0

# [EasyURL] [23.1.1] - Compatibilité déclarée et chaîne qualité

Description : Version de maintenance. Elle corrige neuf paramètres de requête lus sans type — filtres de date de la liste, pagination, export de raccourcis — aligne les bornes de compatibilité Dolibarr du module sur la ligne réellement livrée, et place le code sous analyse statique à chaque modification.

**Cette version demande Saturne 23.2.1 ou supérieur.**

## Améliorations & corrections

### Liste et export des raccourcis

* **Neuf paramètres de requête étaient lus sans être convertis en entiers** avant d'être passés à des fonctions du cœur qui en attendent : filtres de date de la liste des raccourcis, numéro de page, identifiant d'enregistrement à l'édition du libellé, et nombre de lignes de l'export. Un paramètre inattendu produisait un résultat silencieusement faux plutôt qu'une erreur.

### Compatibilité

* Le module déclare **Dolibarr 23 au minimum et 24 au maximum**. Il annonçait la 16 comme plancher, une ligne qu'il ne sait plus faire fonctionner : **sur un Dolibarr antérieur à la 23, il refusera désormais de s'activer** plutôt que de s'installer pour tomber en erreur ensuite.

### Documentation du module

* Le changelog reprend le nom attendu par Dolibarr, `ChangeLog.md`. Le cœur le lit pour l'injecter dans la documentation générée du module ; sous l'ancien nom, il ne le trouvait pas sur un serveur Linux.

### Intégration continue

* Les pull requests passent désormais **PHPStan**, un **lint PHP** et un contrôle de **parité des fichiers de langue** français / anglais : une clé ajoutée d'un côté et oubliée de l'autre arrête la chaîne.
* Les bouchons de test du socle sortent du périmètre analysé : ils masquaient les vraies classes du cœur et rendaient l'analyse plus verte qu'elle ne l'était. Ce sont les neuf erreurs révélées par ce garde-fou qui sont corrigées ci-dessus, plutôt que gelées.

## Comparaison des versions [23.1.0](https://github.com/Eoxia/EasyURL/compare/23.1.0...23.1.1) et 23.1.1

* #165 [Mod] fix: renommer le changelog en ChangeLog.md [`259eeb9`](https://github.com/Eoxia/EasyURL/commit/259eeb9)
* #162 [CI] rework: élaguer les entrées mortes de la baseline [`2781fa7`](https://github.com/Eoxia/EasyURL/commit/2781fa7)
* #159 [CI] rework: scanner le socle par dossier plutôt que l'exclure par morceaux [`8724b14`](https://github.com/Eoxia/EasyURL/commit/8724b14)
* #155 [CI] fix: exclure les bouchons phan de Saturne de l'analyse [`6517eda`](https://github.com/Eoxia/EasyURL/commit/6517eda)
* #151 [CI] fix: deux garde-fous PHPStan, et les neuf erreurs qu'ils révèlent [`f7f75c5`](https://github.com/Eoxia/EasyURL/commit/f7f75c5)
* #147 [CI] rework: aligner phpstan.neon sur le gabarit commun [`8ed6f4e`](https://github.com/Eoxia/EasyURL/commit/8ed6f4e)
* [CI] fix: compléter les dossiers du coeur vus par PHPStan [`b2e8ee8`](https://github.com/Eoxia/EasyURL/commit/b2e8ee8)
* [CI] rework: aligner phpstan.neon sur le gabarit commun [`74fc172`](https://github.com/Eoxia/EasyURL/commit/74fc172)
* #145 [CI] feat: PHPStan, lint PHP et parité des langues [`76e1592`](https://github.com/Eoxia/EasyURL/commit/76e1592)
* #143 [Module] rework: bornes de version Dolibarr 23 minimum, 24 maximum [`f3599a4`](https://github.com/Eoxia/EasyURL/commit/f3599a4)

# [EasyURL] [23.2.0] - Méthode Saturne : des URL courtes sans serveur

Description : EasyURL raccourcit désormais vos liens sans serveur YOURLS. La nouvelle méthode « Saturne » sert les URL courtes depuis votre propre Dolibarr : rien à installer, aucune clé d'API.

**Cette version s'utilise avec Saturne 23.5.0**, qui fournit les URL courtes. Avec un Saturne plus ancien, seule la méthode YOURLS est disponible.

## 🎬 La nouveauté en vidéo

[![Des URL courtes sans serveur, en vidéo (31 s)](https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.2.0/video_poster_23.2.0.jpeg)](https://github.com/Eoxia/EasyURL/releases/download/23.2.0/easyurl-23.2.0.mp4)

*Cliquez sur l'image pour voir la vidéo (31 s).*

## Nouvelles fonctionnalités et innovations

### Méthode de raccourcissement Saturne

* **Des URL courtes sans serveur YOURLS.** La méthode « Saturne (URL courte interne, sans serveur) » s'appuie sur les URL courtes de Saturne : `https://mon-dolibarr.fr/custom/saturne/s/?k7m2xp`, ou plus court encore avec la racine courte de Saturne.
* **Méthode par défaut réglable.** Dans la configuration, choisissez YOURLS ou Saturne : la méthode s'applique aux liens générés automatiquement (paiement, signature) et est proposée par défaut dans l'outil de génération. Sans réglage, rien ne change : YOURLS reste la méthode par défaut.
* **Génération en masse.** L'outil de génération crée des lots d'URL courtes Saturne, prêtes à imprimer en QR codes et à affecter plus tard à vos objets.
* **Le QR imprimé reste valable.** Affecter ou réaffecter un lien Saturne change seulement sa destination : le code, et donc le QR code déjà imprimé, ne bougent pas.
* **Vos liens YOURLS ne bougent pas.** La méthode d'un lien se déduit de son adresse : les liens déjà créés continuent de passer par YOURLS, même si la configuration passe à Saturne.

![Outil de génération en méthode Saturne](https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.2.0/tools.png)

![Configuration : méthode de raccourcissement par défaut](https://raw.githubusercontent.com/nicolas-eoxia/EasyURL/refs/heads/assets/release-23.2.0/setup.png)

## Améliorations & corrections

### Raccourcis

* **L'enregistrement d'une fiche de raccourci ne s'arrête plus sur une erreur technique.** Le module cherchait une table de catégories qui n'existe pas pour les raccourcis : chaque enregistrement affichait « Dolibarr a détecté une erreur technique ».
* **La méthode WordPress est retirée** : proposée dans les listes, elle n'avait jamais été implémentée et ne produisait aucun lien.

### Affectation d'un QR code

* **Compatible avec les liens signés de DigiQuali 23.2.0** : le lien de l'historique public affecté à un QR code porte la signature de DigiQuali, et la page d'affectation lit les deux formes de lien.
* **Seuls des objets Dolibarr peuvent être ouverts depuis l'adresse** de la page publique d'affectation.

## Testez les nouveautés maintenant

**Allez tester la méthode Saturne sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.1.1](https://github.com/Eoxia/EasyURL/compare/23.1.1...23.2.0) et 23.2.0

* #178 [Shortener] feat: méthode Saturne, URL courtes internes sans serveur YOURLS [`#179`](https://github.com/Eoxia/EasyURL/pull/179)
* #180 [Shortener] fix: pas de gestion des catégories, la table llx_categorie_shortener n'existe pas [`#181`](https://github.com/Eoxia/EasyURL/pull/181)
* #182 [Shortener] fix: suivre le déplacement de l'entrée des URL courtes Saturne (public/s → s) [`#183`](https://github.com/Eoxia/EasyURL/pull/183)
* #184 [PublicShortener] fix: track_id signé de DigiQuali à l'affectation, décodage des deux formes [`#185`](https://github.com/Eoxia/EasyURL/pull/185)
* #186 [Shortener] fix: retirer la méthode WordPress, jamais implémentée [`#187`](https://github.com/Eoxia/EasyURL/pull/187)

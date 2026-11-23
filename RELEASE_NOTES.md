# [EasyURL] [23.3.1] - Les fichiers suivent la validation des pièces - Clics propres à chaque QR code

Description : à la validation d'une proposition commerciale, d'une commande, d'un contrat, d'une intervention ou d'une facture, les pièces jointes et l'index des documents suivent de nouveau la référence définitive. Chaque QR code d'un objet affiche ses propres clics, et la page publique d'affectation retrouve son apparence.

**Cette version demande Saturne 23.9.1 ou supérieur.**

## Améliorations & corrections

### Validation des pièces : les fichiers suivent la référence définitive

* Avec la génération automatique des liens courts activée, valider une **proposition commerciale, une commande, un contrat, une intervention ou une facture** laissait ses fichiers sous la référence provisoire « (PROVn) » :
  * les pièces jointes d'une proposition restaient dans le dossier provisoire et n'apparaissaient plus dans l'onglet Fichiers joints de la proposition validée, qui ne listait que son PDF ;
  * l'index des documents d'une facture restait sur le dossier provisoire.
* Le dossier suit de nouveau la référence définitive, pièces jointes et index compris, et le lien de signature ou de paiement est toujours posé à la validation.
* Cette version ne déplace pas les fichiers des pièces validées avant la mise à jour.

### QR codes

* **Chaque QR code affiche ses propres clics**, dans l'écran d'affectation comme dans la liste de la fiche d'un objet : toutes les lignes affichaient le nombre de clics du dernier QR code affecté.
* **Désaffecter un QR code met à jour le lien de l'objet** : il passe au dernier QR code encore affecté, ou se vide s'il n'en reste aucun. Il gardait l'adresse du QR code désaffecté. Un lien qui montre un autre QR code n'est pas touché.
* La fenêtre d'affectation de la fiche d'une URL courte ne produit plus d'avertissement PHP « Undefined array key "easyurl_shortener" ».

### Page publique d'affectation

* La page publique d'affectation ne prend plus le thème du back-office : elle retrouve la police de l'onglet QR code de la page publique de DigiQuali.
* Le logo de la société ne s'affiche plus deux fois en tête de page.

### Stabilité et compatibilité

* Installé dans une racine alternative à `custom/`, EasyURL retrouve la bibliothèque de DigiQuali (lien signé vers l'historique public d'un lot) et l'onglet QR code de la page publique de DigiQuali.
* Plus d'erreur fatale sur les pages qui annulent un formulaire quand EasyURL est actif (par exemple la fiche d'une enveloppe DoliLetter).
* Le pictogramme d'EasyURL s'affiche dans Configuration › Modules › Déclencheurs : c'était une image cassée.
* À l'activation du module, les documents d'EasyURL restés dans un dossier « (PROVn) » rejoignent celui de leur référence définitive (avec Saturne 23.9.1).

## Testez les nouveautés maintenant

**Allez tester la validation d'une proposition commerciale et l'affectation des QR codes sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [23.3.0](https://github.com/Eoxia/EasyURL/compare/23.3.0...23.3.1) et 23.3.1

* [#243] [Mod] fix: fichiers des objets validés restés sous (PROVn) repris à l'activation depuis Accueil > Modules [`eeaa23e9`](https://github.com/Eoxia/EasyURL/commit/eeaa23e9)
* [#244] [Trigger] fix: picto easyurl_color@easyurl, l'image easyurl@easyurl n'existe pas [`70cbb70a`](https://github.com/Eoxia/EasyURL/commit/70cbb70a)
* [#241] [Hook] fix: doActions accepte $action nul [`681e63f7`](https://github.com/Eoxia/EasyURL/commit/681e63f7)
* [#239] [Trigger] fix: validation d'une propale ou d'une facture, le dossier (PROVn) suit de nouveau la référence [`247f795c`](https://github.com/Eoxia/EasyURL/commit/247f795c)
* [#232] [Shortener] fix: chaque QR code d'un objet affiche ses propres clics [`24265c9f`](https://github.com/Eoxia/EasyURL/commit/24265c9f)
* [#231] [Shortener] fix: la désaffectation d'un QR code met à jour le lien EasyURL de l'objet [`8c89d195`](https://github.com/Eoxia/EasyURL/commit/8c89d195)
* [#228] [Module] fix: bibliothèque de DigiQuali et route de l'onglet QR code trouvées en racine alternative [`b1fa6c71`](https://github.com/Eoxia/EasyURL/commit/b1fa6c71)
* [#230] [Shortener] fix: « Undefined array key "easyurl_shortener" » sur shortener_card.php?action=edit_assign [`35e0b9b4`](https://github.com/Eoxia/EasyURL/commit/35e0b9b4)
* [#229] [PublicInterface] fix: public_shortener.php sans le thème back-office de Saturne [`ccdc588b`](https://github.com/Eoxia/EasyURL/commit/ccdc588b)
* [#227] [PublicInterface] fix: logo de la société affiché une seule fois sur la page publique d'affectation [`74f54ed8`](https://github.com/Eoxia/EasyURL/commit/74f54ed8)

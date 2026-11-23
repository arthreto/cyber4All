# Recette fonctionnelle – Branche Groupe-B (Espace compréhension SIO)

CYBER4ALL – BTS SIO SLAM – 23/11/2026

Ce document complète la recette fonctionnelle (tests 1 à 193). Il couvre ce que la branche **Groupe-B** ajoute par rapport à `main` : 46 commits, du 05/10/2026 au 23/11/2026.

Fonctionnalités couvertes :
- connexion et inscription ;
- leçons par thème ;
- quiz Vrai / Faux côté public ;
- Espace Traçabilité côté admin : questions, quiz, leçons, extraits, images et modal « Nouvelle question ».

**Comment les tests ont été faits.** Tous les tests ont été exécutés sur l'environnement local (WAMP, `http://localhost/cyber4All/public`) le 23/11/2026, de trois façons :
- dans le navigateur, pour l'affichage et le JavaScript ;
- par requêtes HTTP (curl / fetch), pour la validation côté serveur et les valeurs falsifiées ;
- avec trois profils : visiteur, contributeur et administrateur.

**Précondition commune** (sauf mention contraire) : application accessible et données disponibles.

## Bilan

| | Nombre |
|---|---|
| Tests exécutés | 102 |
| Conformes au premier passage | 90 |
| Non conformes au premier passage | 11 |
| **Corrigés puis validés le 23/11/2026** | **9** (230 était une remarque ; 233, 247, 287, 296, 298, 312, 316, 319) |
| **Restant non conformes** | **3** (228, 245 : Post/Redirect/Get ; 331 : données) |

### Non-conformités relevées au premier passage (par priorité)

Toutes ont été corrigées le 23/11/2026, sauf 228, 245 et 331 (voir le tableau de recette).

| Priorité | Tests | Problème | Correction proposée |
|---|---|---|---|
| Haute (sécurité) | 287, 298, 316 | Une leçon, un quiz ou une question se suppriment avec une simple adresse **GET**, sans jeton CSRF (exemple : `/floribondant/theme/lecons/supprimer/3`). Une image `<img src="…/supprimer/3">` placée sur n'importe quel site suffit à supprimer un élément si un admin connecté l'affiche. | Dans `leconSupprimer`, `quizSupprimer` et `questionSupprimer`, refuser tout ce qui n'est pas un POST, comme le fait déjà `extraitSupprimer`. Mieux : déclarer ces routes en `post()` dans `Routes.php`. |
| Haute | 296 | Créer un quiz avec un identifiant de question inexistant affiche « Le quiz n'a pas pu être enregistré », mais le quiz **est créé et publié** avec 4 questions sur 5 : le quiz est inséré, puis l'erreur arrive sur la 5ᵉ liaison. | Mettre la création du quiz et de ses liaisons dans une transaction (`transStart` / `transComplete`), comme pour les questions. Vérifier aussi que les questions existent et sont actives. |
| Moyenne | 312 | Une question est enregistrée avec un thème inexistant (`id_theme=999` envoyé à la main). Elle n'apparaît dans aucun thème. | Vérifier que le thème existe, comme le fait déjà `AdminLeconController`. |
| Moyenne | 233 | Si une question d'un quiz est supprimée, le quiz public affiche 4 questions au lieu de 5. L'admin indique toujours « 5 questions » et « Actif ». | Signaler le quiz incomplet dans la liste admin, ou le masquer côté public tant qu'il n'a pas son nombre de questions. |
| Moyenne | 228, 245 | Rafraîchir la page après l'envoi d'un quiz ou après la connexion renvoie le formulaire, ce qui provoque l'erreur CSRF 403 (même défaut que les tests 39, 54, 115…). | Appliquer le modèle Post/Redirect/Get : enregistrer le résultat en session (flashdata), rediriger, puis afficher en GET. |
| Basse | 247 | `/inscription` une fois connecté affiche une page vide, sans message. | Rediriger vers « Mon compte », ou afficher « Vous êtes déjà connecté ». |
| Basse | 319 | `/floribondant/theme/questions/modifier/999999` affiche un formulaire vide au lieu d'une page introuvable. Les leçons, quiz et extraits renvoient bien une 404. | Lever `PageNotFoundException` quand la question n'existe pas. |
| Données | 331 | Le quiz « Ceci est un titre sympa » est actif avec 2 questions, alors que la règle impose 5 à 20 questions. | Compléter ce quiz ou le désactiver. |

### Remarques (pas bloquantes)
- **Adresses des quiz** : corrigé (test 230). Un quiz désactivé garde sa position, donc les adresses des autres quiz ne changent plus. **Les leçons ont toujours l'ancien comportement** : supprimer une leçon décale l'adresse des leçons suivantes du thème.
- **Données existantes** : avec le nouveau calcul, le quiz « Révision : authentification et traçabilité » apparaît en « 8 / 9 questions – Incomplet ». L'ancienne liste affichait déjà 8 questions pour 9 prévues.
- **Bibliothèques** : `marked@18` et `dompurify@3` sont chargées depuis le CDN sans version exacte. Une mise à jour peut donc changer le rendu sans prévenir. Il vaut mieux figer la version complète (par exemple `marked@18.0.0`).
- **Fichiers versionnés en trop** : le dossier `node_modules/` (marked, markdown-to-txt…) et des fichiers `writable/debugbar/*.json` sont versionnés dans la branche, alors que la page Leçon utilise le CDN. Il faudrait les ajouter au `.gitignore`.
- **Inscription** : avec un identifiant très court (« ab »), le message « Le mot de passe ne doit pas contenir l'identifiant » s'ajoute presque toujours à celui de l'identifiant (test 240).
- **Valeur codée en dur** : le champ caché `id_quiz` de la page Quiz vaut toujours `3` (`app/Views/quiz/quiz.php`).
- **Extraits** : la colonne « questions » de la liste compte aussi les questions supprimées (désactivées).

## Tableau de recette

### Leçons (visiteur)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 200 | `/theme/1` sans numéro de leçon | Redirection vers la 1ʳᵉ leçon | Conforme | 302 vers `/theme/1/1` |
| 201 | Affichage d'une leçon | Thème, titre, auteur, date de mise à jour, contenu | Conforme | |
| 202 | Numéro de leçon hors bornes (`/theme/1/99`, `/theme/1/0`) | Page introuvable | Conforme | 404 |
| 203 | Thème inexistant ou non numérique (`/theme/999`, `/theme/abc`) | Page introuvable | Conforme | 404 |
| 204 | Lien « Leçon suivante » | Ouvre la leçon n+1 du même thème | Conforme | |
| 205 | Rendu Markdown (titres, gras, tableau) | Mise en forme HTML | Conforme | Rendu dans le navigateur (marked) |
| 206 | Contenu malveillant (`<script>`, `<img onerror>`, lien `javascript:`) | Neutralisé, aucun script exécuté | Conforme | DOMPurify retire les trois |
| 207 | Leçon désactivée | N'apparaît plus côté public | Conforme | 404 à son ancienne adresse |

### Quiz (visiteur)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 210 | `/quiz` sans thème | Quiz du premier thème | Conforme | |
| 211 | Thème inexistant (`/quiz/999`) | Page introuvable | Conforme | 404 |
| 212 | Quiz inexistant (`/quiz/1/99`, `/quiz/1/0`, thème sans quiz `/quiz/4/1`) | Page introuvable | Conforme | 404 |
| 213 | Une question par page | Une seule question visible, boutons Précédent / Suivant | Conforme | |
| 214 | Grille des numéros à droite | Clic sur un numéro = aller à la question ; question courante entourée | Conforme | Sur mobile, la grille passe au-dessus de la question |
| 215 | Barre de progression | Se remplit à chaque réponse | Conforme | 1/2 réponses = barre à 50 % |
| 216 | Terminer avec des questions sans réponse | Envoi bloqué, « Il reste X question(s) sans réponse », retour à la 1ʳᵉ question vide | Conforme | |
| 217 | Envoi sans aucune réponse (sans JavaScript) | Score 0, sans erreur | Conforme | |
| 218 | Correction | Cases vertes (juste) et rouges (faux) ; bonne réponse en vert, réponse fausse cochée en rouge | Conforme | |
| 219 | Réponses falsifiées (`reponses[1]=7`, `reponses=xyz`) | Comptées fausses, aucune erreur PHP | Conforme | |
| 220 | Envoi sans jeton CSRF | Refusé | Conforme | 403 |
| 221 | Quiz désactivé par l'admin | Disparaît de la liste publique | Conforme | |
| 222 | Question avec extrait et image | Extrait et image affichés dans la question | Conforme | |
| 223 | Question supprimée par l'admin | Retirée du quiz public | Conforme | |
| 228 | Rafraîchir la page de résultat | Le résultat reste affiché | **Non conforme** | Le formulaire est renvoyé : CSRF 403. Post/Redirect/Get à appliquer |
| 230 | Adresse d'un quiz après la suppression d'un autre quiz | Chaque quiz garde son adresse ; l'adresse d'un quiz supprimé renvoie une 404 | Conforme (corrigé) | Les quiz désactivés gardent leur position : `/quiz/3/3` et `/quiz/3/4` (désactivés) renvoient une 404, et les autres quiz ne bougent pas |
| 233 | Quiz dont une question est supprimée | Quiz complet, ou signalé incomplet | Conforme (corrigé) | Admin : « 4 / 5 questions – Incomplet » ; le message de suppression cite le quiz concerné ; la question est marquée « Supprimée » dans le formulaire du quiz, qui refuse de l'enregistrer tant qu'elle n'est pas remplacée |

### Connexion

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 240 | Ouverture de la page | Aucun message d'erreur | Conforme | |
| 241 | Champs vides | Un message sous chaque champ | Conforme | |
| 242 | Identifiant inexistant | « Identifiant ou mot de passe incorrect » | Conforme | |
| 243 | Injection SQL (`' OR '1'='1`) | Connexion refusée | Conforme | |
| 244 | XSS dans l'identifiant | Texte échappé | Conforme | |
| 245 | Rafraîchir après la connexion | Pas d'erreur | **Non conforme** | CSRF 403 (même défaut que le test 39) |
| 246 | `/connexion` une fois connecté | « Heureux de te voir : pseudo » | Conforme | |
| 247 | `/inscription` une fois connecté | Message ou redirection | Conforme (corrigé) | Redirection vers « Mon compte » |
| 248 | Contributeur connecté | Accès à « Mon compte » | Conforme | |
| 249 | Déconnexion puis `/compte` | Accès refusé | Conforme | 404 (une redirection vers la connexion serait plus claire) |

### Inscription (validation côté serveur, JavaScript désactivé)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 250 | Identifiant de 2 caractères | « au moins 3 caractères » | Conforme | Voir la remarque sur « ab » |
| 251 | Identifiant de 14 caractères | « au plus 13 caractères » | Conforme | |
| 252 | Mot de passe de 5 caractères | « au moins 13 caractères (5 actuellement) » | Conforme | |
| 253 | Sans minuscule | « au moins une minuscule » | Conforme | |
| 254 | Sans majuscule | « au moins une majuscule » | Conforme | |
| 255 | Sans chiffre | « au moins un chiffre » | Conforme | |
| 256 | 13 caractères sans symbole | « au moins un symbole parmi … » | Conforme | C'était le faux message « 13 caractères » |
| 257 | Symbole non autorisé (€) | « au moins un symbole parmi … » | Conforme | |
| 258 | Mot de passe contenant l'identifiant | « ne doit pas contenir l'identifiant » | Conforme | |
| 259 | Confirmation différente | « Les mots de passe ne correspondent pas » | Conforme | |
| 260 | Confirmation vide | « Confirmez le mot de passe » | Conforme | |
| 261 | Identifiant déjà pris | « Cet identifiant est déjà utilisé » | Conforme | |
| 262 | Email mal formé | « Adresse email invalide » | Conforme | |
| 263 | Formulaire entièrement vide | Trois messages, un par champ obligatoire | Conforme | |
| 264 | Deux inscriptions de suite sans email | Les deux comptes sont créés (email NULL) | Conforme | Le faux « déjà utilisé » ne se produit plus |
| 265 | Messages en direct (avec JavaScript) | Message précis sous le champ, sans « tremblement » | Conforme | |

### Droits d'accès

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 270 | Visiteur sur les 9 adresses `/floribondant/…` testées | Refusé | Conforme | 404 |
| 271 | Contributeur sur 14 adresses `/floribondant/…` (listes, ajouter, supprimer, utilisateurs, mots) | Refusé | Conforme | 404, aucune suppression |
| 272 | Visiteur sur `/compte` | Refusé | Conforme | 404 |
| 273 | Admin sur `/floribondant/utilisateurs` | Page affichée | Conforme | |

### Admin – Extraits

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 275 | Titre et contenu vides | Refusé, deux messages | Conforme | |
| 276 | Type falsifié (hors token / log / session) | Refusé | Conforme | |
| 277 | Titre de 101 caractères | Refusé (100 au maximum) | Conforme | |
| 278 | Création valide | Retour à la liste, extrait en tête de liste | Conforme | |
| 279 | HTML dans le titre (`<b>`) | Texte échappé dans la liste | Conforme | |
| 280 | Suppression par simple lien GET | Refusée | Conforme | Redirection sans suppression |
| 281 | Suppression d'un extrait utilisé | Supprimé, avec un message qui donne le nombre de questions concernées | Conforme | « 1 question(s) n'ont plus d'extrait » |
| 282 | Suppression d'un extrait non utilisé | Supprimé, avec un message | Conforme | |
| 283 | Suppression d'un extrait inexistant | « Cet extrait n'existe pas… » | Conforme | |

### Admin – Leçons

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 284 | Titre de moins de 3 caractères, contenu vide | Refusé | Conforme | |
| 285 | Thème inexistant (valeur falsifiée) | « Le thème choisi n'existe pas » | Conforme | |
| 286 | Création valide | Retour à la liste ; leçon visible côté public | Conforme | |
| 287 | Suppression par simple lien GET (sans CSRF) | Refusée | Conforme (corrigé) | Redirection vers la liste, la leçon reste publiée ; le bouton (POST) supprime toujours |
| 288 | Modification | Visible côté public | Conforme | |
| 289 | Suppression d'un identifiant inexistant | Page introuvable | Conforme | 404 |

### Admin – Quiz

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 290 | 4 questions annoncées | Refusé (5 au minimum) | Conforme | |
| 291 | 21 questions annoncées | Refusé (20 au maximum) | Conforme | |
| 292 | Titre de moins de 14 caractères | Refusé | Conforme | |
| 293 | 5 questions cochées pour 6 annoncées | « doit contenir exactement 6 questions » | Conforme | |
| 294 | Question d'un autre thème | « ne sont pas du thème du quiz » | Conforme | |
| 295 | Même question envoyée 5 fois (falsifié) | Refusé | Conforme | Les doublons sont retirés, donc le nombre de questions est faux |
| 296 | Identifiant de question inexistant ou question supprimée | Refusé, rien n'est enregistré | Conforme (corrigé) | « 1 question(s) cochée(s) n'existent pas ou ont été supprimées » ; aucun quiz créé. L'enregistrement est maintenant fait dans une transaction |
| 297 | Création valide | Retour à la liste ; quiz visible côté public | Conforme | |
| 298 | Suppression par simple lien GET (sans CSRF) | Refusée | Conforme (corrigé) | Le quiz reste publié |
| 299 | Modification avec une question en moins | Refusée | Conforme | |
| 300 | Rafraîchir après enregistrement | Pas de renvoi du formulaire | Conforme | Post/Redirect/Get appliqué côté admin |
| 301 | Thème inexistant (valeur falsifiée) | Refusé | Conforme | Ajouté avec les corrections |

### Admin – Questions

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 310 | Énoncé de moins de 3 caractères, explication vide | Refusé | Conforme | |
| 311 | Bonne réponse falsifiée (`2`) | « La réponse doit être Vrai ou Faux » | Conforme | |
| 312 | Thème inexistant (valeur falsifiée) | Refusé | Conforme (corrigé) | « Le thème choisi n'existe pas. » |
| 313 | Extrait existant d'un autre thème ou inexistant | Refusé | Conforme | |
| 314 | Nouvel extrait sans titre ni contenu | Refusé | Conforme | |
| 315 | Rattachement à un quiz d'un autre thème | Refusé | Conforme | |
| 316 | Suppression par simple lien GET (sans CSRF) | Refusée | Conforme (corrigé) | La question reste dans le quiz |
| 317 | Faux fichier image (texte renommé en `.png`) | « n'est pas une image » | Conforme | |
| 318 | Image de 6 Mo | Refusée (2 Mo au maximum) | Conforme | |
| 319 | Modifier une question inexistante | Page introuvable | Conforme (corrigé) | 404 |
| 320 | Création avec nouvel extrait et image | Question, extrait et image enregistrés | Conforme | |
| 321 | Filtre par thème, tri par énoncé (croissant et décroissant) | Liste filtrée et triée ; questions supprimées en fin de liste | Conforme | |
| 322 | Page hors limite (`?page=99`), tri invalide (`?tri=pirate`) | Pas d'erreur | Conforme | |

### Admin – Modal « Nouvelle question » (formulaire d'un quiz)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 325 | Quiz complet (9/9), clic sur « Créer une nouvelle question » | Pas de modal ; message « décochez-en une » | Conforme | |
| 326 | Décocher une question | Le message disparaît | Conforme | |
| 327 | Ouverture du modal | Formulaire sans en-tête du site ; thème du quiz coché ; bouton Annuler | Conforme | `?modal=1&id_theme=3&quiz=3` |
| 328 | Touche Échap dans le modal | Fermeture, iframe vidée | Conforme | |
| 329 | Enregistrer une question dans le modal | Modal fermé ; question ajoutée cochée dans « Dans ce quiz », avec le badge Vrai/Faux | Conforme | |
| 330 | Jeton CSRF après enregistrement dans le modal | Le jeton du formulaire du quiz est mis à jour | Conforme | |
| 331 | Cohérence des données existantes | Chaque quiz actif a entre 5 et 20 questions | **Non conforme** | « Ceci est un titre sympa » : 2 questions |

## Données de test laissées en base locale

Les tests ont créé des éléments, puis les ont supprimés depuis l'interface. Les quiz, questions et leçons sont seulement **désactivés** (suppression logique), et les comptes ainsi que l'image sont toujours là. Les extraits de test ont été supprimés. Pour tout effacer :

```sql
DELETE FROM quiz_question WHERE id_quiz IN (6, 7, 8) OR id_question IN (35, 36, 37, 38);
DELETE FROM quiz WHERE id IN (6, 7, 8);
DELETE FROM question WHERE id IN (35, 36, 37, 38);
DELETE FROM lecon WHERE id IN (14, 15, 16);
DELETE FROM utilisateurs WHERE pseudo IN ('qatest01', 'qatest02');
```

Image envoyée pendant les tests : `public/images/questions/1795425396_2542284cc919bac43844.png`.

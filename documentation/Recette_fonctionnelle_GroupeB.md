# Recette fonctionnelle – Branche Groupe-B (Espace compréhension SIO)

CYBER4ALL – BTS SIO SLAM – 23/11/2026

Ce document complète la recette fonctionnelle (tests 1 à 193). Il couvre ce que la branche **Groupe-B** ajoute par rapport à `main` :
- connexion, inscription et page « Mon compte » ;
- sécurité de la session ;
- leçons par thème ;
- quiz Vrai / Faux côté public ;
- Espace Traçabilité côté admin : questions, quiz, leçons, extraits, images, modal « Nouvelle question » et page des utilisateurs.

**Comment les tests ont été faits.** Tous les tests ont été exécutés sur l'environnement local (WAMP, `http://localhost/cyber4All/public`), de trois façons :
- dans le navigateur, pour l'affichage et le JavaScript ;
- par requêtes HTTP (curl / fetch), pour la validation côté serveur, les valeurs falsifiées et les cookies ;
- avec trois profils : visiteur, contributeur et administrateur.

**Précondition commune** (sauf mention contraire) : application accessible et données disponibles.

## Bilan

| | Nombre |
|---|---|
| Tests exécutés | 132 |
| Conformes | 117 |
| Non conformes | 15 |

### Non-conformités à corriger (par priorité)

| Priorité | Tests | Problème | Correction proposée |
|---|---|---|---|
| Haute (sécurité) | 340 | L'identifiant de session ne change pas à la connexion : le cookie `ci_session` reçu avant la connexion reste valable après. C'est une fixation de session, l'attaque présentée dans les leçons du thème Session. | Appeler `session()->regenerate(true)` juste après une connexion réussie (`ConnexionController::connexion`) et après une inscription. |
| Haute (sécurité) | 351, 352 | La page « Mon compte » affiche « Modification de compte réussie » et **enregistre un email invalide** (`pasunemail`, `<b>gras</b>`) : le message d'erreur est préparé, mais l'enregistrement a quand même lieu. Le HTML enregistré est ensuite réaffiché sans échappement dans `compteInformations.php` (attribut `value`) et dans la liste admin des utilisateurs (`tabUtilisateurs.php`, colonne email). Un contributeur peut donc y placer du HTML ou du JavaScript. | Dans `CompteController`, arrêter le traitement (`return redirect()->back()`) dès qu'une erreur est trouvée ; valider l'email côté serveur ; échapper `pseudo` et `email` avec `esc()` dans ces deux vues. |
| Moyenne | 343 | Aucune limite de tentatives de connexion : 12 mauvais mots de passe de suite, puis le bon, et la connexion réussit. | Compter les échecs par identifiant et par adresse IP (en session ou en base) et bloquer quelques minutes après 5 échecs. |
| Moyenne | 373 | La page admin « Utilisateurs » n'affiche aucun utilisateur. Les données sont passées à la vue `header` au lieu de la vue `admin/adminUtilisateurs` (`AdminUtilisateurController::index`). | Passer `$data` à `view('admin/adminUtilisateurs', $data)`. |
| Moyenne | 353 | Un email contenant des caractères spéciaux est échappé deux fois dans le formulaire (`esc()` puis `form_input()`) : `&amp;lt;b&amp;gt;`. À chaque enregistrement sans modification, la valeur se dégrade un peu plus. | Retirer le `esc()` dans `formCompte.php` : `form_input()` échappe déjà la valeur. |
| Moyenne | 354, 355, 356 | Pseudo vide, d'un seul caractère ou de 40 caractères : la modification est refusée, mais **aucun message** n'est affiché. | Valider le pseudo (3 à 13 caractères, comme à l'inscription) et afficher l'erreur. |
| Moyenne | 362 | Supprimer une leçon décale l'adresse des leçons suivantes du thème (la leçon D passe de `/theme/4/2` à `/theme/4/1`). Les quiz, eux, gardent leur adresse. | Même principe que pour les quiz : compter aussi les leçons désactivées dans la position, et renvoyer une 404 pour une leçon désactivée. |
| Moyenne | 228, 345 | Rafraîchir la page après l'envoi d'un quiz ou après la connexion renvoie le formulaire, ce qui provoque l'erreur CSRF 403 (même défaut que les tests 39, 54, 115…). | Appliquer le modèle Post/Redirect/Get : enregistrer le résultat en session (flashdata), rediriger, puis afficher en GET. |
| Basse | 360 | Le menu « Comprendre les accès » propose le thème « Test1234 », qui n'a aucune leçon active : le lien mène à une page 404. | N'afficher dans le menu que les thèmes qui ont au moins une leçon active. |
| Basse | 361 | La page des quiz d'un thème sans quiz (`/quiz/4`) affiche une liste vide sans message, et son lien « Revoir les leçons du thème » mène à une 404. | Afficher « Aucun quiz pour ce thème pour l'instant », et masquer le lien si le thème n'a pas de leçon. |
| Données | 331 | Le quiz « Ceci est un titre sympa » est actif avec 2 questions, alors que la règle impose 5 à 20 questions. Le quiz « Révision : authentification et traçabilité » est signalé « 8 / 9 questions – Incomplet ». | Compléter ces deux quiz ou les désactiver. |

### Remarques (pas bloquantes)
- **Cookie de session** : `HttpOnly` et `SameSite=Lax` sont bien présents (test 341). `Secure` est absent, ce qui est normal en HTTP local, mais il faudra l'activer en production (HTTPS).
- **Accès refusé** : `/compte` sans être connecté renvoie une 404. Une redirection vers la connexion serait plus claire (tests 249, 342).
- **Bibliothèques** : `marked@18` et `dompurify@3` sont chargées depuis le CDN sans version exacte. Une mise à jour peut donc changer le rendu sans prévenir. Il vaut mieux figer la version complète (par exemple `marked@18.0.0`).
- **Fichiers versionnés en trop** : le dossier `node_modules/` (marked, markdown-to-txt…) et des fichiers `writable/debugbar/*.json` sont versionnés dans la branche, alors que la page Leçon utilise le CDN. Il faudrait les ajouter au `.gitignore`.
- **Inscription** : avec un identifiant très court (« ab »), le message « Le mot de passe ne doit pas contenir l'identifiant » s'ajoute presque toujours à celui de l'identifiant (test 250).
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
| 207 | Leçon désactivée | N'apparaît plus côté public | Conforme | |

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
| 224 | Titre de quiz contenant `<script>` | Affiché en texte, rien n'est exécuté | Conforme | |
| 225 | Énoncé (`<img onerror>`) et explication (`<script>`) d'une question | Affichés en texte dans la question et dans la correction | Conforme | |
| 228 | Rafraîchir la page de résultat | Le résultat reste affiché | **Non conforme** | Le formulaire est renvoyé : CSRF 403. Post/Redirect/Get à appliquer |
| 230 | Adresse d'un quiz après la suppression d'un autre quiz | Chaque quiz garde son adresse ; l'adresse d'un quiz supprimé renvoie une 404 | Conforme | `/quiz/3/3` et `/quiz/3/4` (désactivés) renvoient une 404, les autres quiz ne bougent pas |
| 231 | Réactivation d'un quiz supprimé | Il revient à la même adresse | Conforme | `/quiz/3/5` de nouveau accessible |
| 233 | Quiz dont une question est supprimée | Quiz signalé incomplet côté admin | Conforme | « 4 / 5 questions – Incomplet » dans la liste ; le message de suppression cite le quiz concerné |

### Connexion et session

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 240 | Ouverture de la page | Aucun message d'erreur | Conforme | |
| 241 | Champs vides | Un message sous chaque champ | Conforme | |
| 242 | Identifiant inexistant | « Identifiant ou mot de passe incorrect » | Conforme | |
| 243 | Injection SQL (`' OR '1'='1`) | Connexion refusée | Conforme | |
| 244 | XSS dans l'identifiant | Texte échappé | Conforme | |
| 246 | `/connexion` une fois connecté | « Heureux de te voir : pseudo » | Conforme | |
| 247 | `/inscription` une fois connecté | Redirection | Conforme | Vers « Mon compte » |
| 248 | Contributeur connecté | Accès à « Mon compte » | Conforme | |
| 249 | Déconnexion puis `/compte` | Accès refusé | Conforme | 404 (voir remarques) |
| 340 | Identifiant de session à la connexion | Un nouvel identifiant est attribué (protection contre la fixation de session) | **Non conforme** | Le cookie `ci_session` d'avant la connexion reste valable : aucun nouveau cookie n'est envoyé à la connexion |
| 341 | Attributs du cookie de session | `HttpOnly`, `SameSite` | Conforme | `HttpOnly; SameSite=Lax` (pas de `Secure` en HTTP local) |
| 342 | Réutiliser l'ancien cookie après la déconnexion | Plus d'accès au compte | Conforme | `/compte` en 404 |
| 343 | 12 mauvais mots de passe de suite, puis le bon | Blocage temporaire après plusieurs échecs | **Non conforme** | Aucune limite : la 13ᵉ tentative connecte l'utilisateur |
| 345 | Rafraîchir après la connexion | Pas d'erreur | **Non conforme** | CSRF 403 (même défaut que le test 39) |

### Inscription (validation côté serveur, JavaScript désactivé)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 250 | Identifiant de 2 caractères | « au moins 3 caractères » | Conforme | Voir la remarque sur « ab » |
| 251 | Identifiant de 14 caractères | « au plus 13 caractères » | Conforme | |
| 252 | Mot de passe de 5 caractères | « au moins 13 caractères (5 actuellement) » | Conforme | |
| 253 | Sans minuscule | « au moins une minuscule » | Conforme | |
| 254 | Sans majuscule | « au moins une majuscule » | Conforme | |
| 255 | Sans chiffre | « au moins un chiffre » | Conforme | |
| 256 | 13 caractères sans symbole | « au moins un symbole parmi … » | Conforme | |
| 257 | Symbole non autorisé (€) | « au moins un symbole parmi … » | Conforme | |
| 258 | Mot de passe contenant l'identifiant | « ne doit pas contenir l'identifiant » | Conforme | |
| 259 | Confirmation différente | « Les mots de passe ne correspondent pas » | Conforme | |
| 260 | Confirmation vide | « Confirmez le mot de passe » | Conforme | |
| 261 | Identifiant déjà pris | « Cet identifiant est déjà utilisé » | Conforme | |
| 262 | Email mal formé | « Adresse email invalide » | Conforme | |
| 263 | Formulaire entièrement vide | Trois messages, un par champ obligatoire | Conforme | |
| 264 | Deux inscriptions de suite sans email | Les deux comptes sont créés (email NULL) | Conforme | |
| 265 | Messages en direct (avec JavaScript) | Message précis sous le champ, sans « tremblement » | Conforme | |

### Mon compte (contributeur)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 350 | Changer de pseudo | Pseudo modifié, connexion possible avec le nouveau pseudo | Conforme | |
| 351 | Email invalide (`pasunemail`) | Refusé avec un message | **Non conforme** | « Modification de compte réussie » et email enregistré |
| 352 | Email contenant du HTML (`<b>gras</b>`) | Refusé, ou affiché en texte | **Non conforme** | Enregistré ; balise affichée sans échappement sur la page du compte, et sans échappement dans la colonne email de la liste admin des utilisateurs |
| 353 | Enregistrer sans rien changer, avec un email contenant `<` ou `&` | Email inchangé | **Non conforme** | La valeur est échappée deux fois dans le formulaire et se dégrade à chaque enregistrement (`&amp;amp;amp;lt;…`) |
| 354 | Pseudo vide | Refusé avec un message | **Non conforme** | Refusé, mais aucun message |
| 355 | Pseudo d'un seul caractère | Refusé avec un message | **Non conforme** | Refusé, mais aucun message |
| 356 | Pseudo de 40 caractères | Refusé avec un message | **Non conforme** | Refusé, mais aucun message |
| 357 | Ajouter un email valide | Email enregistré | Conforme | |
| 358 | Prendre l'email d'un autre compte | Refusé : « est déjà utilisée » | Conforme | |
| 359 | Prendre le pseudo d'un autre compte | Refusé : « déjà existant » | Conforme | |
| 346 | Case « Supprimer mon email » | Email retiré | Conforme | |
| 347 | Envoi sans jeton CSRF | Refusé | Conforme | 403 |

### Navigation

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 360 | Liens du menu « Comprendre les accès » | Chaque thème ouvre sa première leçon | **Non conforme** | Thèmes 1 à 3 : OK ; « Test1234 » (sans leçon active) : 404 |
| 361 | Page des quiz d'un thème sans quiz (`/quiz/4`) | Message « aucun quiz » | **Non conforme** | Liste vide sans message ; le lien « Revoir les leçons du thème » mène à une 404 |
| 362 | Adresse d'une leçon après la suppression d'une leçon précédente | La leçon garde son adresse | **Non conforme** | La leçon D passe de `/theme/4/2` à `/theme/4/1` ; `/theme/4/2` renvoie une 404 |

### Droits d'accès

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 270 | Visiteur sur les 9 adresses `/floribondant/…` testées | Refusé | Conforme | 404 |
| 271 | Contributeur sur 14 adresses `/floribondant/…` en GET (listes, ajouter, supprimer, utilisateurs, mots) | Refusé | Conforme | 404, aucune suppression |
| 272 | Visiteur sur `/compte` | Refusé | Conforme | 404 |
| 274 | Contributeur en POST, avec un jeton CSRF valide, sur 4 actions admin (supprimer un quiz, une question, un extrait ; ajouter une leçon) | Refusé | Conforme | 404, rien n'est modifié |

### Admin – Utilisateurs

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 373 | Liste des utilisateurs (`/floribondant/utilisateurs`) | Tableau des utilisateurs | **Non conforme** | Aucun utilisateur affiché, ni avec la recherche |

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
| 287 | Suppression par simple lien GET (sans CSRF) | Refusée | Conforme | Redirection vers la liste, la leçon reste publiée |
| 288 | Modification | Visible côté public | Conforme | |
| 289 | Suppression d'un identifiant inexistant | Page introuvable | Conforme | 404 |
| 365 | Titre de 151 caractères | Refusé (150 au maximum) | Conforme | |

### Admin – Quiz

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 290 | 4 questions annoncées | Refusé (5 au minimum) | Conforme | |
| 291 | 21 questions annoncées | Refusé (20 au maximum) | Conforme | |
| 292 | Titre de moins de 14 caractères | Refusé | Conforme | |
| 293 | 5 questions cochées pour 6 annoncées | « doit contenir exactement 6 questions » | Conforme | |
| 294 | Question d'un autre thème | « ne sont pas du thème du quiz » | Conforme | |
| 295 | Même question envoyée 5 fois (falsifié) | Refusé | Conforme | Les doublons sont retirés, donc le nombre de questions est faux |
| 296 | Identifiant de question inexistant ou question supprimée | Refusé, rien n'est enregistré | Conforme | « 1 question(s) cochée(s) n'existent pas ou ont été supprimées » ; aucun quiz créé |
| 297 | Création valide | Retour à la liste ; quiz visible côté public | Conforme | |
| 298 | Suppression par simple lien GET (sans CSRF) | Refusée | Conforme | Le quiz reste publié |
| 299 | Modification avec une question en moins | Refusée | Conforme | |
| 300 | Rafraîchir après enregistrement | Pas de renvoi du formulaire | Conforme | Post/Redirect/Get appliqué côté admin |
| 301 | Thème inexistant (valeur falsifiée) | Refusé | Conforme | |
| 302 | Question supprimée encore liée au quiz | Marquée « Supprimée » dans le formulaire ; enregistrement refusé tant qu'elle n'est pas remplacée | Conforme | |
| 366 | Titre de 151 caractères et description de 1001 caractères | Refusés | Conforme | Deux messages |

### Admin – Questions

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 310 | Énoncé de moins de 3 caractères, explication vide | Refusé | Conforme | |
| 311 | Bonne réponse falsifiée (`2`) | « La réponse doit être Vrai ou Faux » | Conforme | |
| 312 | Thème inexistant (valeur falsifiée) | Refusé | Conforme | « Le thème choisi n'existe pas. » |
| 313 | Extrait existant d'un autre thème ou inexistant | Refusé | Conforme | |
| 314 | Nouvel extrait sans titre ni contenu | Refusé | Conforme | |
| 315 | Rattachement à un quiz d'un autre thème | Refusé | Conforme | |
| 316 | Suppression par simple lien GET (sans CSRF) | Refusée | Conforme | La question reste dans le quiz |
| 317 | Faux fichier image (texte renommé en `.png`) | « n'est pas une image » | Conforme | |
| 318 | Image de 6 Mo | Refusée (2 Mo au maximum) | Conforme | |
| 319 | Modifier une question inexistante | Page introuvable | Conforme | 404 |
| 320 | Création avec nouvel extrait et image | Question, extrait et image enregistrés | Conforme | |
| 321 | Filtre par thème, tri par énoncé (croissant et décroissant) | Liste filtrée et triée ; questions supprimées en fin de liste | Conforme | |
| 322 | Page hors limite (`?page=99`), tri invalide (`?tri=pirate`) | Pas d'erreur | Conforme | |
| 367 | Énoncé de 1001 caractères | Refusé (1000 au maximum) | Conforme | |
| 368 | Énoncé contenant `<img onerror>` | Affiché en texte dans la liste admin | Conforme | |
| 369 | Réactiver une question supprimée (case « active ») | Elle retrouve ses quiz ; le quiz redevient complet | Conforme | « 4 / 5 – Incomplet » redevient « 5 questions » |
| 370 | Modifier une question sans toucher à ses quiz | Ses liens avec les quiz, même désactivés, sont gardés | Conforme | |

### Admin – Modal « Nouvelle question » (formulaire d'un quiz)

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 325 | Quiz complet (9/9), clic sur « Créer une nouvelle question » | Pas de modal ; message « décochez-en une » | Conforme | |
| 326 | Décocher une question | Le message disparaît | Conforme | |
| 327 | Ouverture du modal | Formulaire sans en-tête du site ; thème du quiz coché ; bouton Annuler | Conforme | `?modal=1&id_theme=3&quiz=3` |
| 328 | Touche Échap dans le modal | Fermeture, iframe vidée | Conforme | |
| 329 | Enregistrer une question dans le modal | Modal fermé ; question ajoutée cochée dans « Dans ce quiz », avec le badge Vrai/Faux | Conforme | |
| 330 | Jeton CSRF après enregistrement dans le modal | Le jeton du formulaire du quiz est mis à jour | Conforme | |

### Données

| ID | Fonctionnalité / Cas de test | Résultat attendu | Résultat obtenu | Observations |
|---|---|---|---|---|
| 331 | Cohérence des quiz existants | Chaque quiz actif a entre 5 et 20 questions, toutes actives | **Non conforme** | « Ceci est un titre sympa » : 2 questions ; « Révision : authentification et traçabilité » : 8 / 9 |

## Données de test laissées en base locale

Les tests ont créé des éléments préfixés « QA », puis les ont supprimés depuis l'interface. Les quiz, questions et leçons sont seulement **désactivés** (suppression logique) ; les deux comptes de test et une image sont toujours là. Pour tout effacer :

```sql
DELETE FROM quiz_question WHERE id_quiz IN (SELECT id FROM quiz WHERE titre LIKE 'QA%')
                             OR id_question IN (SELECT id FROM question WHERE libelle LIKE 'QA%');
DELETE FROM quiz WHERE titre LIKE 'QA%';
DELETE FROM question WHERE libelle LIKE 'QA%';
DELETE FROM lecon WHERE titre LIKE 'QA%';
DELETE FROM utilisateurs WHERE pseudo IN ('qatest01b', 'qatest02b');
```

Image envoyée pendant les tests : `public/images/questions/1795425396_2542284cc919bac43844.png`.

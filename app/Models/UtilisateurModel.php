<?php

namespace App\Models;

use App\Entities\Utilisateur;
use App\Models\MotCle\MotCleValidationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Ce model représente la gestion des Utilisateur de la table utilisateurs
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class UtilisateurModel extends Model
{
    protected $table = 'utilisateurs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'pseudo', 'mot_de_passe', 'email', 'role'];
    protected $returnType = Utilisateur::class;

    /**
     * Insère un nouvel utilisateur dans la table `utilisateurs`.
     *
     * Cette méthode prépare les données de l'objet `Utilisateur` passé en paramètre
     * et tente de les insérer dans la table. Si l'insertion réussit, l'identifiant
     * (`id`) du nouvel utilisateur est retourné. En cas d'erreur (par exemple
     * violation de clé unique ou problème de connexion à la base), un message est
     * logué via `log_message('error', ...)` et la méthode retourne `false`.
     *
     * @param Utilisateur $utilisateur Objet contenant les informations du nouvel utilisateur :
     *   - pseudo : Pseudo de l'utilisateur (string)
     *   - email : Adresse email (string|null)
     *   - mot_de_passe : Mot de passe hashé (string)
     *   - role : Rôle de l'utilisateur (string, ex. "admin" ou "contributeur")
     *   - date_creation : Date de création (DateTime|null)
     *
     * @return int|false ID du nouvel utilisateur si l'insertion réussit, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function insertUtilisateur(Utilisateur $utilisateur): int|false
    {
        $data = [
            'pseudo' => $utilisateur->pseudo,
            'email' => $utilisateur->email,
            'mot_de_passe' => $utilisateur->mot_de_passe,
            'role' => $utilisateur->role,
            'date_creation' => $utilisateur->date_creation,
        ];

        try {
            return $this->insert($data);
        } catch (DatabaseException $e) {
            log_message('error', "Erreur insertUtilisateur : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un utilisateur de la table `utilisateurs` en fonction de son identifiant.
     *
     * Cette méthode tente de supprimer l'enregistrement correspondant à l'identifiant
     * `$id` dans la table `utilisateurs`. Si la suppression réussit, elle retourne `true`.
     * En cas d'erreur (par exemple problème de connexion à la base ou contrainte de clé
     * étrangère), un message est logué via `log_message('error', ...)` et la méthode
     * retourne `false`.
     *
     * @param int $id Identifiant unique de l'utilisateur à supprimer.
     * @return bool `true` si la suppression a réussi, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function deleteUtilisateurById(int $id): bool
    {
        try {
            return $this->delete($id);
        } catch (DatabaseException $e) {
            log_message('error', "Erreur deleteUtilisateurById : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour les informations d'un utilisateur existant dans la table `utilisateurs`.
     *
     * Cette méthode prend un objet `Utilisateur` et un tableau `$data` contenant
     * les champs à mettre à jour. Elle tente de mettre à jour l'enregistrement
     * correspondant à l'identifiant de l'utilisateur. Si la mise à jour réussit,
     * la méthode retourne `true`. En cas d'erreur (ex. problème de connexion à la
     * base ou violation de contrainte), un message est logué via `log_message('error', ...)`
     * et la méthode retourne `false`.
     *
     * @param array $data Tableau associatif des champs à mettre à jour, par exemple :
     *   [
     *     'pseudo' => 'nouveau_pseudo',
     *     'email' => 'nouveau_email@example.com'
     *   ]
     * @param Utilisateur $utilisateur Objet utilisateur représentant l'enregistrement à mettre à jour.
     *
     * @return bool `true` si la mise à jour a réussi, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function updateUtilisateurByID(array $data, Utilisateur $utilisateur): bool
    {
        try {
            return $this->update($utilisateur->id, $data);
        } catch (DatabaseException $e) {
            log_message('error', "Erreur updateUtilisateurByID : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur à partir de son identifiant unique.
     *
     * Cette méthode effectue une requête sur la table `utilisateurs` pour trouver
     * l'enregistrement correspondant à l'identifiant `$id`. Si l'utilisateur est trouvé,
     * un objet `Utilisateur` est retourné. Si aucun résultat n'est trouvé ou en cas
     * d'erreur de base de données, la méthode retourne `null` et logue l'erreur.
     *
     * @param int $id Identifiant unique de l'utilisateur à récupérer.
     *
     * @return Utilisateur|null L'objet Utilisateur si trouvé, `null` sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getUtilisateurById(int $id): ?Utilisateur
    {
        try {
            return $this->find($id);
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getUtilisateurById : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère un utilisateur à partir de son pseudo.
     *
     * Cette méthode recherche dans la table `utilisateurs` un enregistrement
     * correspondant au pseudo fourni. Si trouvé, elle retourne un objet
     * `Utilisateur`. Si aucun résultat n'est trouvé ou en cas d'erreur,
     * elle retourne `false`.
     *
     * @param string $pseudo Pseudo de l'utilisateur à récupérer.
     * @return Utilisateur|false Objet Utilisateur si trouvé, false sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getUtilisateurParPseudo(string $pseudo): Utilisateur|false
    {
        try {
            $result = $this->where('pseudo', $pseudo)->first();
            return $result instanceof Utilisateur ? $result : false;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getUtilisateurParPseudo : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie la validité du pseudo et du mot de passe d'un utilisateur.
     *
     * Cette méthode récupère l'utilisateur correspondant au pseudo donné et
     * compare le mot de passe fourni avec le mot de passe haché stocké.
     * Si la vérification réussit, elle retourne un objet `Utilisateur`.
     * Sinon, elle retourne `null` et logue l'erreur.
     *
     * @param string $pseudo Pseudo de l'utilisateur.
     * @param string $mdp Mot de passe à vérifier.
     * @return Utilisateur|null Objet Utilisateur si authentification réussie, null sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function verifierUtilisateur(string $pseudo, string $mdp): ?Utilisateur
    {
        try {
            $utilisateur = $this->where('pseudo', $pseudo)->first();
            if ($utilisateur && password_verify($mdp, $utilisateur->mot_de_passe)) :
                $utilisateur = new Utilisateur([
                    'id' => $utilisateur->id,
                    'pseudo' => $utilisateur->pseudo,
                    'email' => $utilisateur->email,
                    'role' => $utilisateur->role,
                    'date_creation' => $utilisateur->date_creation,
                ]);
                $utilisateur->setScore($this->getScoreContributeur($utilisateur));

                if ($utilisateur->role === "admin"):
                    log_message('info', "L\'utilisateur admin : $utilisateur->pseudo , 
                    $utilisateur->id s'est connecté");
                endif;
                return $utilisateur;
            endif;
            log_message('error', "Erreur connexion : pseudo ou mot de passe incorrect pour '{$pseudo}'");
            return null;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur verifierUtilisateur : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie si une adresse email existe déjà dans la table `utilisateurs`.
     *
     * @param string $email Adresse email à vérifier.
     * @return bool True si l'adresse existe, false sinon.
     *
     * @since 1.0.0
     *
     * @author Benjamin
     */
    public function adresseEmailExiste(string $email): bool
    {
        try {
            return $this->where('email', $email)->countAllResults() > 0;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur adresseEmailExiste : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un pseudo existe déjà dans la table `utilisateurs`.
     *
     * @param string $pseudo Pseudo à vérifier.
     * @return bool True si le pseudo existe, false sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function pseudoExiste(string $pseudo): bool
    {
        try {
            return $this->where('pseudo', $pseudo)->countAllResults() > 0;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur pseudoExiste : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le classement des utilisateurs par nombre total de votes.
     *
     * Cette méthode compte les votes par utilisateur, les trie par ordre
     * décroissant et retourne un tableau des utilisateurs les plus actifs.
     *
     * @param int $nbUtilisateurs Nombre maximal d'utilisateurs à retourner.
     * @return array Tableau contenant les objets utilisateurs et leur nombre de votes.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getClassementVotes(int $nbUtilisateurs): array
    {
        try {
            $users = $this->select('*, COUNT(vote.id_vote) AS nb_votes')
                ->join('vote', 'vote.id_utilisateur = utilisateurs.id', 'INNER')
                ->where('utilisateurs.role', 'contributeur')
                ->groupBy('vote.id_utilisateur')
                ->orderBy('nb_votes', 'DESC')
                ->findAll($nbUtilisateurs);

            foreach ($users as $user) {
                $user->setScore($this->getScoreContributeur($user));
            }

            return $users;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getClassementVotes : " . $e->getMessage());
            return [];
        }
    }

    /**
     * getMotClePaginatedOrderBy permet de récupérer tous les mots dans l'ordre passé en paramètre
     * @param int $perPage
     * @param int|null $statut
     * @return array
     */
    public function getUtilisateursPaginatedOrderBy(?string $ordre = "", ?string $recherche = "", int $perPage = 10, ?int $statut = null): array
    {
        try {
            // attribue à l'ordre codé l'ordre réel en bdd
            if (!empty($ordre)) {
                $champEtOrdre = $this->setchampEtOrdre($ordre);
                $champ = $champEtOrdre[0];
                $ordre = $champEtOrdre[1];
                $champ = in_array($champ, $this->allowedFields) ? $champ : "date_creation";
            } else {
                $champ = 'date_creation';
                $ordre = 'ASC';
            }
            $recherche = trim($recherche);

            // vérifie que le champ fait bien partie des champs accessibles sur la table
            $champ = in_array($champ, $this->allowedFields) ? $champ : "date_creation";
            $this->groupStart()
                ->like('utilisateurs.pseudo', $recherche)
                ->orLike('utilisateurs.email', $recherche)
                ->orLike('utilisateurs.date_creation', $recherche)
                ->orLike('utilisateurs.id', $recherche)
                ->groupEnd();

            if ($champ === 'pseudo') {
                // trie avec les accents
                $this->orderBy('utilisateurs.pseudo COLLATE utf8mb4_unicode_ci', $ordre, false);
            } else {
                $this->orderBy($champ, $ordre);
            }
            return $this
                // trie avec les accents
                ->paginate($perPage); // paginer par le nombre de mot à afficher

        } catch (DatabaseException $e) {
            log_message('error', "Erreur getUtilisateursPaginatedOrderBy : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Permet d'attribuer le bon champ avec le bon ordre avec l'abreviation passer en parametre
     * @param string $ordre
     * @return string[] [ordre, champ]
     */
    private function setChampEtOrdre(string $ordre): array
    {
        // attribue à l'ordre codé l'ordre réel en bdd
        switch ($ordre) {
            case 'ida':
                $champ = 'id';
                $ordre = 'ASC';
                break;
            case 'idd':
                $champ = 'id';
                $ordre = 'DESC';
                break;
            case 'roa':
                $champ = 'role';
                $ordre = 'ASC';
                break;
            case 'rod':
                $champ = 'role';
                $ordre = 'DESC';
                break;
            case 'psa':
                $champ = 'pseudo';
                $ordre = 'ASC';
                break;
            case 'psd':
                $champ = 'pseudo';
                $ordre = 'DESC';
                break;
            case 'ema':
                $champ = 'email';
                $ordre = 'ASC';
                break;
            case 'emd':
                $champ = 'email';
                $ordre = 'DESC';
                break;
            case 'dca':
                $champ = 'date_creation';
                $ordre = 'ASC';
                break;
            case 'dcd':
                $champ = 'date_creation';
                $ordre = 'DESC';
                break;
            default:
                $champ = 'date_creation';
                $ordre = 'DESC';
        }
        // vérifie que le champ fait bien partie des champs accessibles sur la table
        $champ = in_array($champ, $this->allowedFields) ? $champ : "date_ajout";
        return [$champ, $ordre];
    }

    /**
     * Permet de retourner le nombre d'années d'ancienneté d'un utilisateur
     * @param int $idUtilisateur id de l'utilisateur
     * @return int
     */
    private function getAncieneteUtilisateur(int $idUtilisateur): int
    {
        try {
            $row = $this->asArray()
                ->select('TIMESTAMPDIFF(YEAR, utilisateurs.date_creation, CURDATE()) AS anciennete')
                ->where('utilisateurs.id', $idUtilisateur)
                ->find();
            return isset($row['anciennete']) ? (int)$row['anciennete'] : 0;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getAncieneteUtilisateur : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * setScoreContributeur permet de calculer le score d'un contributeur en
     * fonction de son nombre de votes, de mot clé ajouter et valider
     * par les admins ainsi que de son ancienneté.
     * @param Utilisateur $utilisateur
     * @return void
     */
    public function getScoreContributeur(Utilisateur $utilisateur): int
    {
        if ($utilisateur->role === "contributeur") {
            $utilisateur_model = new UtilisateurModel();
            $motCleValidation_model = new MotCleValidationModel();
            $vote_model = new VoteModel();

            $score = 0;
            // socre lié au nombre de vote
            $score += $vote_model->getNbVotesTotalByUtilisateurId($utilisateur->id) * SCORE_VOTE;
            // score lié au nombre de mot clé valider par les admin
//            $score += $motCleValidation_model->getNbMotCleValidationContributeur($utilisateur->id) * SCORE_MOT;
            // score lié à l'inscription
            $score += $utilisateur_model->getAncieneteUtilisateur($utilisateur->id) * SCORE_ANCIENNETE + SCORE_INSCRIPTION;
            return $score;
        } else {

            return 0;
        }
    }
}
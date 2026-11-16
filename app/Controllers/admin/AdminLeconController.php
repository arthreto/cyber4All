<?php

namespace App\Controllers\admin;

use App\Entities\LeconForm;
use App\Models\LeconModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

/**
 * Ce controller gère les leçons de l'Espace compréhension SIO (administration) :
 * liste, ajout / modification et suppression.
 *
 * Le contrôle de l'administrateur et les méthodes des listes sont dans AdminController.
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Raphael BIERLAIRE
 */
class AdminLeconController extends AdminController
{
    protected LeconModel $lecon_model;
    protected UtilisateurModel $utilisateur_model;

    public function __construct()
    {
        // Constructeur du parent : session, ThemeModel et vérification de l'administrateur
        parent::__construct();

        $this->lecon_model = new LeconModel();
        $this->utilisateur_model = new UtilisateurModel();
    }

    /**
     * Liste des leçons : filtre par thème, tri et pagination.
     *
     * @return string
     * @author Raphael BIERLAIRE
     */
    public function index()
    {
        $base = 'floribondant/theme/lecons';

        // Colonnes du tableau : texte de l'en-tête, puis les phrases qui décrivent le tri
        $colonnes = [
            'titre'      => ['entete' => 'Titre', 'phrase' => 'par titre', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'theme'      => ['entete' => 'Thème', 'phrase' => 'par thème', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'auteur'     => ['entete' => 'Auteur', 'phrase' => 'par auteur', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'date_ajout' => ['entete' => 'Ajoutée le', 'phrase' => 'par date d\'ajout', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
            'date_modif' => ['entete' => 'Dernière modification', 'phrase' => 'par date de modification', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
        ];

        // 1. Paramètres de l'URL (exemple : ?id_theme=2&tri=titre&ordre=asc&page=2)
        $themeSelect = (int) ($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, array_keys($colonnes), true)) {
            $tri = null; // colonne inconnue ou absente : ordre par défaut
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        // 2. Leçons du thème choisi (null = tous les thèmes), triées par le modèle
        $idTheme = null;
        if ($themeSelect > 0) {
            $idTheme = $themeSelect;
        }
        $lecons = $this->lecon_model->getAllLecons($idTheme, $tri, $ordre);

        // Leçon qui vient d'être ajoutée ou modifiée (envoyée par leconAjouter), en premier ;
        // les leçons inactives (supprimées) à la fin
        $idNouveau = (int) ($this->session->getFlashdata('id_nouveau') ?? 0);
        $lecons = $this->ordonnerListe($lecons, $idNouveau);

        // 3. Pagination : 20 leçons par page
        $parPage = 20;
        $totalLecons = count($lecons);
        $nbPages = max(1, (int) ceil($totalLecons / $parPage));

        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $nbPages) {
            $page = $nbPages;
        }

        // 4. Liens pour la vue (méthodes privées en bas du contrôleur) : on garde le tri quand on change de thème, et le thème quand on change de tri
        $parametresTri = [];
        if ($tri !== null) {
            $parametresTri = ['tri' => $tri, 'ordre' => $ordre];
        }
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }

        // Un formulaire de leçon resté en session ne sert plus quand on revient à la liste
        $this->session->remove('leconForm');

        $data = [
            'onglet'       => 'lecons',
            'themeSelect'  => $themeSelect,
            'leconList'    => array_slice($lecons, ($page - 1) * $parPage, $parPage),
            'idNouveau'    => $idNouveau,
            'totalLecons'  => $totalLecons,
            'filtreThemes' => $this->filtreThemes($base, $this->theme_model->getAllThemes(), $themeSelect, $parametresTri),
            'colonnes'     => $this->colonnesTri($base, $colonnes, $tri, $ordre, $parametresTheme),
            'messageTri'   => $this->messageTri('Leçons triées', $colonnes, $tri, $ordre),
            'urlSansTri'   => $this->adresseListe($base, $parametresTheme),
            'pages'        => $this->pagesListe($base, $nbPages, $page, $parametresTheme + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminLecons', $data)
            . view('footer');
    }

    /**
     * « Suppression » d'une leçon : elle n'est pas effacée de la base, elle passe inactive (actif = 0).
     * Elle n'est plus affichée sur le site et s'affiche à la fin de la liste.
     * Pour la réactiver : la modifier et cocher « Leçon active ».
     *
     * @param int $idLecon Identifiant de la leçon à désactiver
     * @return \CodeIgniter\HTTP\RedirectResponse Redirection vers la liste des leçons, avec un message
     *
     * @author Raphael BIERLAIRE
     */
    public function leconSupprimer(int $idLecon)
    {
        if ($this->lecon_model->getLeconById($idLecon) === null) {
            throw PageNotFoundException::forPageNotFound('Leçon introuvable');
        }

        if ($this->lecon_model->updateLecon(['actif' => 0], $idLecon)) {
            return redirect()->to(site_url('floribondant/theme/lecons'))
                ->with('success', 'Leçon supprimée : elle est désactivée et rangée à la fin de la liste.');
        }

        return redirect()->to(site_url('floribondant/theme/lecons'))
            ->with('error', "La leçon n'a pas pu être supprimée. Veuillez réessayer.");
    }

    /**
     * Ajout ou modification d'une leçon.
     *
     * L'auteur est enregistré à la création (id_auteur),
     * le dernier modificateur à chaque modification (id_auteur_modif).
     *
     * @param int|null $idLeconModif Identifiant de la leçon à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Raphael BIERLAIRE
     */
    public function leconAjouter(?int $idLeconModif = null)
    {
        $form = $this->session->get('leconForm');

        // Le formulaire en session concerne une autre leçon : on repart de zéro
        if ($form !== null && (int) ($form->modifier ?? 0) !== (int) ($idLeconModif ?? 0)) {
            $form = null;
        }

        if ($form === null) {
            $form = new LeconForm();

            if ($idLeconModif !== null) {
                $lecon = $this->lecon_model->getLeconById($idLeconModif);

                if ($lecon === null) {
                    throw PageNotFoundException::forPageNotFound('Leçon introuvable');
                }

                $form->fill($lecon->toArray());
                $form->modifier = $idLeconModif;

                // Informations en lecture seule affichées par la vue
                $form->auteur       = $this->getPseudo($lecon->id_auteur);
                $form->auteur_modif = $this->getPseudo($lecon->id_auteur_modif);
                $form->date_ajout   = $this->formaterDate($lecon->date_ajout);
                $form->date_modif   = $this->formaterDate($lecon->date_modif);
            }
        }

        $errors = [];

        if ($this->request->is('post')) {
            // Uniquement les champs du formulaire (ni jeton CSRF, ni action, ni champ ajouté à la main)
            $form->fill($this->request->getPost(['id_theme', 'titre', 'contenu', 'actif']));

            $action = $this->request->getPost('action');

            if ($action === 'enregistrer') {
                $formData = [
                    'id_theme' => $this->request->getPost('id_theme') ?? '',
                    'titre'    => trim($this->request->getPost('titre') ?? ''),
                    // Pas de trim sur le contenu : les espaces font partie de la mise en forme Markdown
                    'contenu'  => $this->request->getPost('contenu') ?? '',
                    'actif'    => $this->request->getPost('actif') ?? '0',
                ];

                $rules = [
                    'id_theme' => [
                        'label' => 'Thème',
                        'rules' => 'required|is_natural_no_zero',
                        'errors' => [
                            'required' => 'Veuillez choisir un thème.',
                            'is_natural_no_zero' => 'Le thème choisi est invalide.',
                        ],
                    ],
                    'titre' => [
                        'label' => 'Titre',
                        'rules' => 'required|min_length[3]|max_length[150]',
                        'errors' => [
                            'required' => 'Veuillez renseigner un titre.',
                            'min_length' => 'Le titre doit contenir au moins 3 caractères.',
                            'max_length' => 'Le titre ne peut pas dépasser 150 caractères.',
                        ],
                    ],
                    'contenu' => [
                        'label' => 'Contenu',
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Veuillez renseigner le contenu de la leçon.',
                        ],
                    ],
                    'actif' => [
                        'label' => 'Statut',
                        'rules' => 'required|in_list[0,1]',
                        'errors' => [
                            'required' => 'Veuillez définir le statut de la leçon.',
                            'in_list' => 'Le statut de la leçon est invalide.',
                        ],
                    ],
                ];

                if (!$this->validateData($formData, $rules)) {
                    $errors = $this->validator->getErrors();
                } elseif ($this->theme_model->find((int) $formData['id_theme']) === null) {
                    // Le numéro est valide, mais aucun thème ne porte cet id
                    $errors['id_theme'] = "Le thème choisi n'existe pas.";
                }

                if (empty($errors)) {
                    $estModification = !empty($form->modifier);

                    // Id de la leçon enregistrée (false si l'enregistrement échoue)
                    $idLecon = false;

                    if ($estModification) {
                        $formData['id_auteur_modif'] = $this->utilisateur->id;
                        if ($this->lecon_model->updateLecon($formData, (int) $form->modifier)) {
                            $idLecon = (int) $form->modifier;
                        }
                    } else {
                        $formData['id_auteur'] = $this->utilisateur->id;
                        $idLecon = $this->lecon_model->creerLecon($formData);
                    }

                    if ($idLecon !== false) {
                        $this->session->remove('leconForm');

                        return redirect()->to(site_url('floribondant/theme/lecons'))
                            ->with('id_nouveau', $idLecon) // affichée en premier dans la liste
                            ->with('success', $estModification
                                ? 'Leçon modifiée avec succès !'
                                : 'Leçon créée avec succès !');
                    }

                    $errors['enregistrement'] = "La leçon n'a pas pu être enregistrée. Veuillez réessayer.";
                }
            }

            $this->session->set('leconForm', $form);
        }

        $data = [
            'form' => $form,
            'themeList' => $this->theme_model->getAllThemes(),
            'errors' => $errors,
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formLecon', $data)
            . view('footer');
    }

    /**
     * Retourne le pseudo d'un utilisateur à partir de son identifiant.
     *
     * @param int|null $idUtilisateur Identifiant de l'utilisateur, ou null.
     * @return string|null Pseudo de l'utilisateur, ou null si aucun id ou utilisateur introuvable.
     *
     * @author Raphael BIERLAIRE
     */
    private function getPseudo(int|null $idUtilisateur): ?string
    {
        if ($idUtilisateur === null) {
            return null;
        }

        return $this->utilisateur_model->getUtilisateurById((int) $idUtilisateur)?->pseudo;
    }

    /**
     * Formate une date de la base (texte ou objet Time) au format jj/mm/aaaa hh:mm.
     *
     * @param mixed $date Date à formater (chaîne, objet Time ou null).
     * @return string|null Date formatée, ou null si aucune date.
     *
     * @author Raphael BIERLAIRE
     */
    private function formaterDate(mixed $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Time::parse((string) $date)->format('d/m/Y H:i');
    }
}

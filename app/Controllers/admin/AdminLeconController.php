<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\LeconForm;
use App\Entities\Utilisateur;
use App\Models\LeconModel;
use App\Models\ThemeModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Session\Session;

/**
 * Ce controller permet l'ajout, la modification et la suppression des leçons
 * de l'Espace compréhension SIO (administration).
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Raphael BIERLAIRE
 */
class AdminLeconController extends BaseController
{
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;
    protected ThemeModel $theme_model;
    protected LeconModel $lecon_model;
    protected UtilisateurModel $utilisateur_model;
    public function __construct()
    {
        $this->session = session();

        $this->theme_model = new ThemeModel();
        $this->lecon_model = new LeconModel();
        $this->utilisateur_model = new UtilisateurModel();

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;
        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Suppression d'une leçon
     *
     * @param int $idLecon Identifiant de la leçon à supprimer
     * @return \CodeIgniter\HTTP\RedirectResponse Redirection vers la liste des leçons, avec un message
     */
    public function leconSupprimer(int $idLecon)
    {
        if ($this->lecon_model->getLeconById($idLecon) === null) {
            throw PageNotFoundException::forPageNotFound('Leçon introuvable');
        }

        if ($this->lecon_model->supprimerLecon($idLecon)) {
            return redirect()->to(site_url('floribondant/theme/lecons'))
                ->with('success', 'Leçon supprimée avec succès !');
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
            $form->fill($this->request->getPost(['id_theme', 'titre', 'contenu']));

            $action = $this->request->getPost('action');

            if ($action === 'enregistrer') {
                $formData = [
                    'id_theme' => $this->request->getPost('id_theme') ?? '',
                    'titre'    => trim($this->request->getPost('titre') ?? ''),
                    // Pas de trim sur le contenu : les espaces font partie de la mise en forme Markdown
                    'contenu'  => $this->request->getPost('contenu') ?? '',
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
                ];

                if (!$this->validateData($formData, $rules)) {
                    $errors = $this->validator->getErrors();
                } elseif ($this->theme_model->find((int) $formData['id_theme']) === null) {
                    // Le numéro est valide, mais aucun thème ne porte cet id
                    $errors['id_theme'] = "Le thème choisi n'existe pas.";
                }

                if (empty($errors)) {
                    $estModification = !empty($form->modifier);

                    if ($estModification) {
                        $formData['id_auteur_modif'] = $this->utilisateur->id;
                        $succes = $this->lecon_model->updateLecon($formData, (int) $form->modifier);
                    } else {
                        $formData['id_auteur'] = $this->utilisateur->id;
                        $succes = $this->lecon_model->creerLecon($formData) !== false;
                    }

                    if ($succes) {
                        $this->session->remove('leconForm');

                        return redirect()->to(site_url('floribondant/theme/lecons'))
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
     */
    private function formaterDate(mixed $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Time::parse((string) $date)->format('d/m/Y H:i');
    }
}
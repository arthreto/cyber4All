<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\ThemeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

/**
 * Contrôleur parent de l'administration de l'Espace compréhension SIO.
 *
 * Les contrôleurs des questions, des quiz et des leçons héritent de celui-ci (extends AdminController) :
 * - le constructeur vérifie que l'utilisateur connecté est administrateur ;
 * - les méthodes du bas préparent les liens des listes (filtre par thème, tri, pagination).
 *
 * « abstract » : on ne l'utilise jamais seul, aucune route ne pointe vers lui.
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Arthur
 * @author Raphael Bierlaire
 */
abstract class AdminController extends BaseController
{
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;
    protected ThemeModel $theme_model;

    public function __construct()
    {
        $this->session = session();
        $this->theme_model = new ThemeModel();

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;
        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /* =====================================================================
     * Méthodes utilisées par les trois listes (leçons, questions, quiz).
     * « protected » : elles servent aux contrôleurs enfants (AdminQuestionController,
     * AdminQuizController, AdminLeconController), mais aucune route ne peut les appeler.
     *
     * Elles préparent ce que les vues affichent : liens du filtre par thème,
     * en-têtes de colonnes triables, phrase du tri et numéros de page.
     * ===================================================================== */

    /**
     * Adresse d'une liste avec ses paramètres d'URL.
     * Exemple : $this->adresseListe('floribondant/theme/lecons', ['tri' => 'titre', 'ordre' => 'asc'])
     * donne http://.../floribondant/theme/lecons?tri=titre&amp;ordre=asc
     *
     * Le & est écrit &amp; car l'adresse est placée dans un attribut HTML (href).
     *
     * @param string $base       Adresse de la liste, sans paramètres
     * @param array  $parametres Paramètres d'URL [nom => valeur]
     * @return string
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function adresseListe(string $base, array $parametres = []): string
    {
        if ($parametres === []) {
            return site_url($base);
        }

        return site_url($base) . '?' . http_build_query($parametres, '', '&amp;');
    }

    /**
     * Liens du filtre par thème : "Tous les thèmes", puis un lien par thème.
     *
     * @param string $base          Adresse de la liste
     * @param array  $themeList     Thèmes (objets avec id et nom)
     * @param int    $themeSelect   Thème affiché, -1 pour tous
     * @param array  $parametresTri Tri en cours, conservé quand on change de thème
     * @return array Liste de liens ['nom' => ..., 'url' => ..., 'actif' => true si c'est le thème affiché]
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function filtreThemes(string $base, array $themeList, int $themeSelect, array $parametresTri): array
    {
        $liens = [];

        $liens[] = [
            'nom'   => 'Tous les thèmes',
            'url'   => $this->adresseListe($base, $parametresTri),
            'actif' => $themeSelect <= 0,
        ];

        foreach ($themeList as $theme) {
            $parametres = $parametresTri;
            $parametres['id_theme'] = (int) $theme->id;

            $liens[] = [
                'nom'   => $theme->nom,
                'url'   => $this->adresseListe($base, $parametres),
                'actif' => (int) $theme->id === $themeSelect,
            ];
        }

        return $liens;
    }

    /**
     * En-têtes du tableau. Un clic sur une colonne triable donne :
     * ordre croissant, puis décroissant, puis retour à l'ordre par défaut.
     *
     * Chaque colonne est décrite par : entete (texte affiché), et si elle est triable :
     * phrase (« par titre »), asc (« de A à Z ») et desc (« de Z à A »).
     *
     * @param string      $base            Adresse de la liste
     * @param array       $colonnes        Colonnes [clé => description]
     * @param string|null $tri             Colonne triée, null pour l'ordre par défaut
     * @param string      $ordre           Sens du tri : asc ou desc
     * @param array       $parametresTheme Thème filtré, conservé quand on change de tri
     * @return array Liste d'en-têtes ['texte', 'url' (null si non triable), 'infobulle', 'aria_sort']
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function colonnesTri(string $base, array $colonnes, ?string $tri, string $ordre, array $parametresTheme): array
    {
        $entetes = [];

        foreach ($colonnes as $cle => $colonne) {
            // Colonne non triable : texte seul
            if (! isset($colonne['phrase'])) {
                $entetes[] = ['texte' => $colonne['entete'], 'url' => null, 'infobulle' => '', 'aria_sort' => 'none'];
                continue;
            }

            if ($tri !== $cle) {
                // Colonne pas encore triée : le clic trie dans l'ordre croissant
                $parametres = ['tri' => $cle, 'ordre' => 'asc'];
                $infobulle  = 'Trier ' . $colonne['phrase'] . ', ' . $colonne['asc'];
                $ariaSort   = 'none';
            } elseif ($ordre === 'asc') {
                // Colonne triée dans l'ordre croissant : le clic passe en décroissant
                $parametres = ['tri' => $cle, 'ordre' => 'desc'];
                $infobulle  = 'Trier ' . $colonne['phrase'] . ', ' . $colonne['desc'];
                $ariaSort   = 'ascending';
            } else {
                // Colonne triée dans l'ordre décroissant : le clic revient à l'ordre par défaut
                $parametres = [];
                $infobulle  = 'Revenir à l\'ordre par défaut';
                $ariaSort   = 'descending';
            }

            // + ajoute les paramètres du thème à ceux du tri
            $entetes[] = [
                'texte'     => $colonne['entete'],
                'url'       => $this->adresseListe($base, $parametres + $parametresTheme),
                'infobulle' => $infobulle,
                'aria_sort' => $ariaSort, // flèche affichée par le CSS selon cette valeur
            ];
        }

        return $entetes;
    }

    /**
     * Phrase qui rappelle le tri en cours, par exemple « Leçons triées par titre, de A à Z. »
     *
     * @param string      $debut    Début de la phrase, par exemple « Leçons triées »
     * @param array       $colonnes Colonnes (même tableau que pour colonnesTri)
     * @param string|null $tri      Colonne triée, null pour l'ordre par défaut
     * @param string      $ordre    Sens du tri : asc ou desc
     * @return string|null La phrase, ou null s'il n'y a pas de tri
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function messageTri(string $debut, array $colonnes, ?string $tri, string $ordre): ?string
    {
        if ($tri === null) {
            return null;
        }

        return $debut . ' ' . $colonnes[$tri]['phrase'] . ', ' . $colonnes[$tri][$ordre] . '.';
    }

    /**
     * Numéros de page. La page affichée n'a pas de lien.
     *
     * @param string $base       Adresse de la liste
     * @param int    $nbPages    Nombre de pages
     * @param int    $page       Page affichée
     * @param array  $parametres Thème et tri en cours, conservés d'une page à l'autre
     * @return array Liste de pages ['numero', 'url', 'actuelle' => true pour la page affichée]
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function pagesListe(string $base, int $nbPages, int $page, array $parametres): array
    {
        $pages = [];

        for ($numero = 1; $numero <= $nbPages; $numero++) {
            $parametresPage = $parametres;

            // La page 1 n'a pas besoin de ?page=1 dans l'adresse
            if ($numero > 1) {
                $parametresPage['page'] = $numero;
            }

            $pages[] = [
                'numero'   => $numero,
                'url'      => $this->adresseListe($base, $parametresPage),
                'actuelle' => $numero === $page,
            ];
        }

        return $pages;
    }
}

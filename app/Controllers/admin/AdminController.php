<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\ExtraitModel;
use App\Models\ThemeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

/**
 * Contrôleur parent de l'administration de l'Espace compréhension SIO.
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

    /**
     * Ordre d'une liste : l'élément ajouté ou modifié juste avant en premier,
     * puis les actifs, puis les inactifs. L'ordre du tri est conservé dans chaque groupe.
     *
     * @param array $liste     Liste déjà triée (tableaux ou objets)
     * @param int   $idNouveau Id de l'élément ajouté ou modifié (0 si aucun)
     * @return array
     *
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function ordonnerListe(array $liste, int $idNouveau): array
    {
        $nouveau = [];
        $actifs = [];
        $inactifs = [];

        foreach ($liste as $element) {
            if (is_array($element)) {
                $id = (int) $element['id'];
                $actif = (int) ($element['actif'] ?? 1);
            } else {
                $id = (int) $element->id;
                $actif = (int) ($element->actif ?? 1);
            }

            if ($idNouveau > 0 && $id === $idNouveau) {
                $nouveau[] = $element;
            } elseif ($actif === 1) {
                $actifs[] = $element;
            } else {
                $inactifs[] = $element;
            }
        }

        return array_merge($nouveau, $actifs, $inactifs);
    }

    /**
     * Adresse d'une liste avec ses paramètres d'URL.
     *
     * @param string $base Adresse de la liste, sans paramètres
     * @param array  $parametres Paramètres d'URL
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
     * Liens du filtre par thème.
     *
     * @param string $base          Adresse de la liste
     * @param array  $themeList     Thèmes (objets avec id et nom)
     * @param int    $themeSelect   Thème affiché, -1 pour tous
     * @param array  $parametresTri Tri en cours, conservé quand on change de thème
     * @return array Liste de liens
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
     * Liens du filtre par type d'extrait
     *
     * @param string      $base          Adresse de la liste
     * @param string|null $typeSelect    Type affiché, null pour tous
     * @param array       $parametresTri Tri en cours, conservé quand on change de type
     * @return array Liste de liens
     * @author Arthur
     */
    protected function filtreTypes(string $base, ?string $typeSelect, array $parametresTri): array
    {
        $liens = [];

        $liens[] = [
            'nom'   => 'Tous les types',
            'url'   => $this->adresseListe($base, $parametresTri),
            'actif' => $typeSelect === null,
        ];

        foreach (ExtraitModel::TYPES as $type => $libelle) {
            $parametres = $parametresTri;
            $parametres['type'] = $type;

            $liens[] = [
                'nom'   => $libelle,
                'url'   => $this->adresseListe($base, $parametres),
                'actif' => $type === $typeSelect,
            ];
        }

        return $liens;
    }

    /**
     * Header du tableau. Un clic sur une colonne triable.
     *
     *
     * @param string      $base            Adresse de la liste
     * @param array       $colonnes        Colonnes
     * @param string|null $tri             Colonne triée, null pour l'ordre par défaut
     * @param string      $ordre           Sens du tri : asc ou desc
     * @param array       $parametresTheme Thème filtré, conservé quand on change de tri
     * @return array Liste du header
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
     * Phrase qui rappelle le tri en cours
     *
     * @param string      $debut    Début de la phrase
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
     * Numéros de page
     *
     * @param string $base       Adresse de la liste
     * @param int    $nbPages    Nombre de pages
     * @param int    $page       Page affichée
     * @param array  $parametres Thème et tri en cours
     * @return array Liste de pages
     * @author Arthur
     * @author Raphael Bierlaire
     */
    protected function pagesListe(string $base, int $nbPages, int $page, array $parametres): array
    {
        $pages = [];

        for ($numero = 1; $numero <= $nbPages; $numero++) {
            $parametresPage = $parametres;

            // La page 1 n'a pas besoin de ?page=1 dans l'adresse pour fonctionné
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

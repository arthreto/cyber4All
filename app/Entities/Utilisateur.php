<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use DateTime;

/**
 * Cette classe représente un Utilisateur.
 *
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class Utilisateur extends Entity
{
    /**
     * Les champs qui doivent être traités comme des dates
     */
    protected $dates = ['date_creation'];

    /**
     * Les attributs par défaut de l'entité
     */
    protected $attributes = [
        'id'            => null,
        'pseudo'        => null,
        'email'         => null,
        'mot_de_passe'  => null,
        'role'          => null,
        'date_creation' => null,

        // relationnel
        'score'         => 0,
    ];

    /**
     * Types de données pour chaque attribut
     */
    protected $casts = [
        'id'            => 'integer',
        'pseudo'        => 'string',
        'email'         => '?string',
        'mot_de_passe'  => '?string',
        'role'          => '?string',
        'date_creation' => '?datetime',
    ];


    /**
     * creer permet de créer un Utilisateur.
     * Il dispose d'un id autoincrémenter par la table utilisateur,
     * d'un pseudo, d'un mot de passe,
     * d'un role, d'un email optionnel nullable.
     * @param string $pseudo Pseudo
     * @param string $email Email
     * @param string $motDePasse Mot de passe
     * @param string $role Role (admin ou contributeur)
     * @param DateTime|null $dateCreation Date de création
     * @return self
     * @author Benjamin Lorieul 
     * @since 1.0.0
     */
    public static function creer(
        string $pseudo,
        string $motDePasse,
        string $role,
        ?string $email = null,
        ?\DateTime $dateCreation = null
    ): ?self
    {
        $utilisateur = new self([
            'pseudo' => $pseudo,
            'email' => $email,
            'role' => $role,
            'date_creation' => $dateCreation,
        ]);

        // Valide et hash le mot de passe
        if (!$utilisateur->setMotDePasse($motDePasse, $pseudo)) {
            return null; // mot de passe invalide
        }

        return $utilisateur;
    }

    /**
     * Définit le mot de passe si toutes les règles sont respectées
     *
     * Règles :
     * - Au moins 1 minuscule
     * - Au moins 1 majuscule
     * - Au moins 1 chiffre
     * - Au moins 1 symbole parmi .*?!%@=$£\[]+
     * - Entre 13 et 255 caractères
     * - Ne doit pas contenir le pseudo
     *
     * @param string $mdp Mot de passe à vérifier
     * @param string $pseudo Pseudo à exclure du mot de passe
     * @return bool true si valide, false sinon
     */
    private function setMotDePasse(string $mdp, string $pseudo): bool
    {
        // Vérifie que le mot de passe ne contient pas le pseudo
        if (stripos($mdp, $pseudo) !== false) {
            return false;
        }

        // Expression régulière compacte :
        // (?=.*[a-z])   -> au moins une minuscule
        // (?=.*[A-Z])   -> au moins une majuscule
        // (?=.*[0-9])   -> au moins un chiffre
        // (?=.*[.*?!%@=$£\[\]+]) -> au moins un symbole autorisé
        // .{1,12}       -> entre 13 et 255 caractères
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[.*?!%@=$£\[\]+]).{13,255}$/';

        if (!preg_match($pattern, $mdp)) {
            return false;
        }

        // Toutes les conditions sont respectées
        $this->attributes['mot_de_passe'] = password_hash($mdp, MDP_HASH);
        return true;
    }

    public function setScore(int $score){
        $this->attributes['score'] = max($score,0);
    }
}

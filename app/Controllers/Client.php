<?php

namespace App\Controllers;

/**
 * Espace du client connecté.
 *
 * NB : à ce stade, seul le système de connexion est implémenté.
 * Les vues "Solde", "Dépôt", "Retrait", "Transfert" et "Historique"
 * viendront compléter cet espace dans les prochaines étapes.
 */
class Client extends BaseController
{
    /**
     * Page d'accueil de l'espace client, affichée juste après la connexion.
     */
    public function tableauDeBord()
    {
        $data = [
            'numero_telephone' => session()->get('numero_telephone'),
        ];

        return view('client/tableau_de_bord', $data);
    }
}

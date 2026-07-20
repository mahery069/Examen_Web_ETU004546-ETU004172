<?php

namespace App\Controllers;

use App\Models\CompteModel;

/**
 * Espace du client connecté.
 *
 * NB : à ce stade, les vues "Dépôt", "Retrait", "Transfert" et
 * "Historique" viendront compléter cet espace dans les prochaines étapes.
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

    /**
     * Affiche le solde actuel du client connecté.
     */
    public function solde()
    {
        $compteModel = new CompteModel();
        $compte      = $compteModel->find(session()->get('compte_id'));

        $data = [
            'numero_telephone' => session()->get('numero_telephone'),
            'solde'            => $compte['solde'] ?? 0,
        ];

        return view('client/solde', $data);
    }
}

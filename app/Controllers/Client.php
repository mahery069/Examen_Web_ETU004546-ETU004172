<?php

namespace App\Controllers;

use App\Models\BaremeFraisModel;
use App\Models\CompteModel;
use App\Models\OperationModel;
use App\Models\TypeOperationModel;

/**
 * Espace du client connecté.
 *
 * NB : à ce stade, les vues "Transfert" et "Historique" viendront
 * compléter cet espace dans les prochaines étapes.
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

    /**
     * Affiche le formulaire de dépôt.
     */
    public function depot()
    {
        return view('client/depot');
    }

    /**
     * Traite le dépôt : crédite le solde du client et enregistre
     * l'opération dans l'historique.
     */
    public function deposer()
    {
        $rules = [
            'montant' => [
                'label' => 'Montant',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'      => 'Veuillez saisir un montant.',
                    'numeric'       => 'Le montant doit être un nombre.',
                    'greater_than'  => 'Le montant doit être supérieur à 0.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('erreurs', $this->validator->getErrors());
        }

        $montant  = (float) $this->request->getPost('montant');
        $compteId = (int) session()->get('compte_id');

        $typeOperationModel = new TypeOperationModel();
        $typeDepot          = $typeOperationModel->trouverParCode('depot');

        $compteModel    = new CompteModel();
        $operationModel = new OperationModel();

        $db = db_connect();
        $db->transStart();

        $compte = $compteModel->find($compteId);
        $compteModel->update($compteId, [
            'solde' => $compte['solde'] + $montant,
        ]);

        $operationModel->insert([
            'compte_id'         => $compteId,
            'type_operation_id' => $typeDepot['id'],
            'montant'           => $montant,
            'frais'             => 0,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('erreur', 'Le dépôt a échoué, veuillez réessayer.');
        }

        return redirect()->to('/client/solde')->with('succes', 'Dépôt de ' . number_format($montant, 2, ',', ' ') . ' Ar effectué avec succès.');
    }

    /**
     * Affiche le formulaire de retrait.
     */
    public function retrait()
    {
        return view('client/retrait');
    }

    /**
     * Traite le retrait : vérifie le solde, applique les frais selon le
     * barème correspondant au montant, débite le compte (montant + frais)
     * et enregistre l'opération.
     */
    public function retirer()
    {
        $rules = [
            'montant' => [
                'label' => 'Montant',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'     => 'Veuillez saisir un montant.',
                    'numeric'      => 'Le montant doit être un nombre.',
                    'greater_than' => 'Le montant doit être supérieur à 0.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('erreurs', $this->validator->getErrors());
        }

        $montant  = (float) $this->request->getPost('montant');
        $compteId = (int) session()->get('compte_id');

        $typeOperationModel = new TypeOperationModel();
        $typeRetrait         = $typeOperationModel->trouverParCode('retrait');

        $baremeFraisModel = new BaremeFraisModel();
        $tranche          = $baremeFraisModel->trouverTranche($typeRetrait['id'], $montant);

        if ($tranche === null) {
            return redirect()->back()->withInput()->with('erreur', "Aucun barème de frais ne correspond à ce montant. Veuillez contacter l'opérateur.");
        }

        $frais = (float) $tranche['frais'];
        $total = $montant + $frais;

        $compteModel = new CompteModel();
        $compte      = $compteModel->find($compteId);

        if ((float) $compte['solde'] < $total) {
            return redirect()->back()->withInput()->with('erreur', 'Solde insuffisant. Montant + frais requis : '
                . number_format($total, 2, ',', ' ') . ' Ar (solde actuel : '
                . number_format((float) $compte['solde'], 2, ',', ' ') . ' Ar).');
        }

        $operationModel = new OperationModel();

        $db = db_connect();
        $db->transStart();

        $compteModel->update($compteId, [
            'solde' => $compte['solde'] - $total,
        ]);

        $operationModel->insert([
            'compte_id'         => $compteId,
            'type_operation_id' => $typeRetrait['id'],
            'montant'           => $montant,
            'frais'             => $frais,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('erreur', 'Le retrait a échoué, veuillez réessayer.');
        }

        return redirect()->to('/client/solde')->with('succes', 'Retrait de '
            . number_format($montant, 2, ',', ' ') . ' Ar (frais : '
            . number_format($frais, 2, ',', ' ') . ' Ar) effectué avec succès.');
    }
}

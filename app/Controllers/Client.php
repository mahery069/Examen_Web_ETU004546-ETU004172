<?php

namespace App\Controllers;

use App\Models\BaremeFraisModel;
use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\OperationModel;
use App\Models\TypeOperationModel;

/**
 * Espace du client connecté.
 */
class Client extends BaseController
{
    /**
     * Nombre de dernières opérations affichées sur le tableau de bord.
     */
    private const NB_OPERATIONS_RECENTES = 5;

    /**
     * Page d'accueil de l'espace client, affichée juste après la connexion.
     * Affiche un résumé : solde actuel et dernières opérations.
     */
    public function tableauDeBord()
    {
        $compteId = (int) session()->get('compte_id');

        $compteModel = new CompteModel();
        $compte      = $compteModel->find($compteId);

        $operationModel = new OperationModel();
        $operations     = $operationModel->historiqueDuCompte($compteId, self::NB_OPERATIONS_RECENTES);

        $data = [
            'numero_telephone' => session()->get('numero_telephone'),
            'solde'            => $compte['solde'] ?? 0,
            'lignes'           => $this->formaterOperations($operations, $compteId),
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

    /**
     * Affiche le formulaire de transfert.
     */
    public function transfert()
    {
        return view('client/transfert');
    }

    /**
     * Traite le transfert entre le compte du client connecté (expéditeur)
     * et le compte d'un autre client (destinataire) : vérifie l'existence
     * du destinataire, applique les frais selon le barème, débite
     * l'expéditeur (montant + frais), crédite le destinataire (montant)
     * et enregistre une opération unique pour les deux comptes.
     */
    public function transferer()
    {
        $rules = [
            'numero_destinataire' => [
                'label' => 'Numéro du destinataire',
                'rules' => 'required|regex_match[/^0[0-9]{9}$/]',
                'errors' => [
                    'required'    => 'Veuillez saisir le numéro du destinataire.',
                    'regex_match' => 'Le numéro doit être composé de 10 chiffres et commencer par 0.',
                ],
            ],
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

        $numeroDestinataire = $this->request->getPost('numero_destinataire');
        $montant             = (float) $this->request->getPost('montant');
        $numeroExpediteur    = session()->get('numero_telephone');
        $compteExpediteurId  = (int) session()->get('compte_id');

        if ($numeroDestinataire === $numeroExpediteur) {
            return redirect()->back()->withInput()->with('erreur', 'Vous ne pouvez pas effectuer un transfert vers votre propre numéro.');
        }

        $clientModel = new ClientModel();
        $destinataire = $clientModel->trouverParNumero($numeroDestinataire);

        if ($destinataire === null) {
            return redirect()->back()->withInput()->with('erreur', "Le numéro \"{$numeroDestinataire}\" ne correspond à aucun client.");
        }

        $compteModel        = new CompteModel();
        $compteDestinataire = $compteModel->trouverParClient($destinataire['id']);

        $typeOperationModel = new TypeOperationModel();
        $typeTransfert       = $typeOperationModel->trouverParCode('transfert');

        $baremeFraisModel = new BaremeFraisModel();
        $tranche          = $baremeFraisModel->trouverTranche($typeTransfert['id'], $montant);

        if ($tranche === null) {
            return redirect()->back()->withInput()->with('erreur', "Aucun barème de frais ne correspond à ce montant. Veuillez contacter l'opérateur.");
        }

        $frais = (float) $tranche['frais'];
        $total = $montant + $frais;

        $compteExpediteur = $compteModel->find($compteExpediteurId);

        if ((float) $compteExpediteur['solde'] < $total) {
            return redirect()->back()->withInput()->with('erreur', 'Solde insuffisant. Montant + frais requis : '
                . number_format($total, 2, ',', ' ') . ' Ar (solde actuel : '
                . number_format((float) $compteExpediteur['solde'], 2, ',', ' ') . ' Ar).');
        }

        $operationModel = new OperationModel();

        $db = db_connect();
        $db->transStart();

        $compteModel->update($compteExpediteurId, [
            'solde' => $compteExpediteur['solde'] - $total,
        ]);

        $compteModel->update($compteDestinataire['id'], [
            'solde' => $compteDestinataire['solde'] + $montant,
        ]);

        $operationModel->insert([
            'compte_id'              => $compteExpediteurId,
            'compte_destinataire_id' => $compteDestinataire['id'],
            'type_operation_id'      => $typeTransfert['id'],
            'montant'                => $montant,
            'frais'                  => $frais,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('erreur', 'Le transfert a échoué, veuillez réessayer.');
        }

        return redirect()->to('/client/solde')->with('succes', 'Transfert de '
            . number_format($montant, 2, ',', ' ') . ' Ar (frais : '
            . number_format($frais, 2, ',', ' ') . ' Ar) vers ' . $numeroDestinataire . ' effectué avec succès.');
    }

    /**
     * Affiche l'historique chronologique des opérations du client
     * connecté (dépôts, retraits, transferts envoyés/reçus).
     */
    public function historique()
    {
        $compteId = (int) session()->get('compte_id');

        $operationModel = new OperationModel();
        $operations     = $operationModel->historiqueDuCompte($compteId);

        return view('client/historique', [
            'lignes' => $this->formaterOperations($operations, $compteId),
        ]);
    }

    /**
     * Transforme une liste brute d'opérations (issue de
     * OperationModel::historiqueDuCompte()) en lignes prêtes à l'affichage :
     * libellé lisible, contrepartie (numéro) et effet signé sur le solde,
     * du point de vue du compte passé en paramètre.
     */
    private function formaterOperations(array $operations, int $compteId): array
    {
        return array_map(static function (array $operation) use ($compteId) {
            $estExpediteur = (int) $operation['compte_id'] === $compteId;

            switch ($operation['type_code']) {
                case 'depot':
                    $libelle      = 'Dépôt';
                    $contrepartie = null;
                    $montantSigne = (float) $operation['montant'];
                    break;

                case 'retrait':
                    $libelle      = 'Retrait';
                    $contrepartie = null;
                    $montantSigne = -((float) $operation['montant'] + (float) $operation['frais']);
                    break;

                case 'transfert':
                    if ($estExpediteur) {
                        $libelle      = 'Transfert envoyé';
                        $contrepartie = $operation['numero_destinataire'];
                        $montantSigne = -((float) $operation['montant'] + (float) $operation['frais']);
                    } else {
                        $libelle      = 'Transfert reçu';
                        $contrepartie = $operation['numero_expediteur'];
                        $montantSigne = (float) $operation['montant'];
                    }
                    break;

                default:
                    $libelle      = $operation['type_libelle'];
                    $contrepartie = null;
                    $montantSigne = (float) $operation['montant'];
            }

            return [
                'libelle'       => $libelle,
                'contrepartie'  => $contrepartie,
                'montant'       => (float) $operation['montant'],
                'frais'         => (float) $operation['frais'],
                'montant_signe' => $montantSigne,
                'date'          => $operation['date_operation'],
            ];
        }, $operations);
    }
}

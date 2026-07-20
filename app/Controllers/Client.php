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
            'numero_telephone'     => session()->get('numero_telephone'),
            'solde'                => $compte['solde'] ?? 0,
            'credit_frais_retrait' => $compte['credit_frais_retrait'] ?? 0,
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
        $compteModel = new CompteModel();
        $compte      = $compteModel->find(session()->get('compte_id'));

        return view('client/retrait', [
            'credit_frais_retrait' => $compte['credit_frais_retrait'] ?? 0,
        ]);
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

        $fraisBareme = (float) $tranche['frais'];

        $compteModel = new CompteModel();
        $compte      = $compteModel->find($compteId);

        // V2 : un crédit de frais de retrait peut avoir été prépayé par
        // l'expéditeur d'un transfert reçu (option "frais de retrait
        // inclus"). Il vient réduire, voire annuler, les frais de ce retrait.
        $creditDisponible = (float) ($compte['credit_frais_retrait'] ?? 0);
        $creditConsomme   = min($creditDisponible, $fraisBareme);
        $frais            = $fraisBareme - $creditConsomme;
        $total            = $montant + $frais;

        if ((float) $compte['solde'] < $total) {
            return redirect()->back()->withInput()->with('erreur', 'Solde insuffisant. Montant + frais requis : '
                . number_format($total, 2, ',', ' ') . ' Ar (solde actuel : '
                . number_format((float) $compte['solde'], 2, ',', ' ') . ' Ar).');
        }

        $operationModel = new OperationModel();

        $db = db_connect();
        $db->transStart();

        $compteModel->update($compteId, [
            'solde'                => $compte['solde'] - $total,
            'credit_frais_retrait' => $creditDisponible - $creditConsomme,
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

        $message = 'Retrait de ' . number_format($montant, 2, ',', ' ') . ' Ar (frais : '
            . number_format($frais, 2, ',', ' ') . ' Ar) effectué avec succès.';

        if ($creditConsomme > 0) {
            $message .= ' Un crédit de frais de retrait prépayé de '
                . number_format($creditConsomme, 2, ',', ' ') . ' Ar a été utilisé.';
        }

        return redirect()->to('/client/solde')->with('succes', $message);
    }

    /**
     * Affiche le formulaire de transfert.
     */
    public function transfert()
    {
        return view('client/transfert');
    }

    /**
     * Règles de validation communes à l'aperçu et à la confirmation du
     * transfert (numéro du destinataire + montant).
     */
    private function reglesTransfert(): array
    {
        return [
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
    }

    /**
     * Vérifie et calcule toutes les données nécessaires à un transfert :
     * existence du destinataire, frais de transfert, frais de retrait
     * estimés (si l'option est demandée) et solde suffisant chez
     * l'expéditeur. Recalculé systématiquement à partir des données
     * fraîches de la base (jamais à partir de valeurs soumises par le
     * client), à l'aperçu comme à la confirmation.
     *
     * @return array{erreur:string}|array{destinataire:array,compte_destinataire:array,compte_expediteur:array,type_transfert_id:int,frais_transfert:float,frais_retrait_estime:float,total_debit:float}
     */
    private function calculerTransfert(
        string $numeroExpediteur,
        int $compteExpediteurId,
        string $numeroDestinataire,
        float $montant,
        bool $inclureFraisRetrait
    ): array {
        if ($numeroDestinataire === $numeroExpediteur) {
            return ['erreur' => 'Vous ne pouvez pas effectuer un transfert vers votre propre numéro.'];
        }

        $clientModel  = new ClientModel();
        $destinataire = $clientModel->trouverParNumero($numeroDestinataire);

        if ($destinataire === null) {
            return ['erreur' => "Le numéro \"{$numeroDestinataire}\" ne correspond à aucun client."];
        }

        $compteModel         = new CompteModel();
        $compteDestinataire  = $compteModel->trouverParClient($destinataire['id']);

        $typeOperationModel = new TypeOperationModel();
        $typeTransfert       = $typeOperationModel->trouverParCode('transfert');
        $typeRetrait         = $typeOperationModel->trouverParCode('retrait');

        $baremeFraisModel  = new BaremeFraisModel();
        $trancheTransfert  = $baremeFraisModel->trouverTranche($typeTransfert['id'], $montant);

        if ($trancheTransfert === null) {
            return ['erreur' => "Aucun barème de frais ne correspond à ce montant. Veuillez contacter l'opérateur."];
        }

        $fraisTransfert     = (float) $trancheTransfert['frais'];
        $fraisRetraitEstime = 0.0;

        if ($inclureFraisRetrait) {
            $trancheRetrait = $baremeFraisModel->trouverTranche($typeRetrait['id'], $montant);

            if ($trancheRetrait === null) {
                return ['erreur' => "Aucun barème de frais de retrait ne correspond à ce montant : l'option \"frais de retrait inclus\" est indisponible pour ce montant."];
            }

            $fraisRetraitEstime = (float) $trancheRetrait['frais'];
        }

        $totalDebit = $montant + $fraisTransfert + $fraisRetraitEstime;

        $compteExpediteur = $compteModel->find($compteExpediteurId);

        if ((float) $compteExpediteur['solde'] < $totalDebit) {
            return ['erreur' => 'Solde insuffisant. Montant total requis : '
                . number_format($totalDebit, 2, ',', ' ') . ' Ar (solde actuel : '
                . number_format((float) $compteExpediteur['solde'], 2, ',', ' ') . ' Ar).'];
        }

        return [
            'destinataire'         => $destinataire,
            'compte_destinataire'  => $compteDestinataire,
            'compte_expediteur'    => $compteExpediteur,
            'type_transfert_id'    => $typeTransfert['id'],
            'frais_transfert'      => $fraisTransfert,
            'frais_retrait_estime' => $fraisRetraitEstime,
            'total_debit'          => $totalDebit,
        ];
    }

    /**
     * Étape 1 : valide le formulaire de transfert et affiche un
     * récapitulatif (montant net reçu, frais de transfert, frais de
     * retrait prépayés éventuels, total débité) avant toute écriture en
     * base.
     */
    public function transfertApercu()
    {
        if (! $this->validate($this->reglesTransfert())) {
            return redirect()->back()->withInput()->with('erreurs', $this->validator->getErrors());
        }

        $numeroDestinataire  = $this->request->getPost('numero_destinataire');
        $montant             = (float) $this->request->getPost('montant');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');

        $resultat = $this->calculerTransfert(
            session()->get('numero_telephone'),
            (int) session()->get('compte_id'),
            $numeroDestinataire,
            $montant,
            $inclureFraisRetrait
        );

        if (isset($resultat['erreur'])) {
            return redirect()->back()->withInput()->with('erreur', $resultat['erreur']);
        }

        return view('client/transfert_apercu', [
            'numero_destinataire'   => $numeroDestinataire,
            'montant'                => $montant,
            'inclure_frais_retrait'  => $inclureFraisRetrait,
            'frais_transfert'        => $resultat['frais_transfert'],
            'frais_retrait_estime'   => $resultat['frais_retrait_estime'],
            'total_debit'            => $resultat['total_debit'],
        ]);
    }

    /**
     * Étape 2 : exécute réellement le transfert (appelé depuis le
     * formulaire de confirmation de l'aperçu). Recalcule tout depuis la
     * base pour ne jamais faire confiance à des montants soumis par le
     * client. Débite l'expéditeur (montant + frais + frais de retrait
     * prépayés éventuels), crédite le destinataire (montant net) et, si
     * l'option est activée, alimente son crédit de frais de retrait pour
     * que son prochain retrait en bénéficie.
     */
    public function transferer()
    {
        if (! $this->validate($this->reglesTransfert())) {
            return redirect()->to('/client/transfert')->with('erreur', 'Une erreur est survenue, veuillez recommencer votre transfert.');
        }

        $numeroDestinataire  = $this->request->getPost('numero_destinataire');
        $montant             = (float) $this->request->getPost('montant');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');
        $compteExpediteurId  = (int) session()->get('compte_id');

        $resultat = $this->calculerTransfert(
            session()->get('numero_telephone'),
            $compteExpediteurId,
            $numeroDestinataire,
            $montant,
            $inclureFraisRetrait
        );

        if (isset($resultat['erreur'])) {
            return redirect()->to('/client/transfert')->with('erreur', $resultat['erreur']);
        }

        $compteDestinataire = $resultat['compte_destinataire'];
        $compteExpediteur   = $resultat['compte_expediteur'];
        $fraisTransfert      = $resultat['frais_transfert'];
        $fraisRetraitEstime  = $resultat['frais_retrait_estime'];
        $totalDebit          = $resultat['total_debit'];

        $compteModel    = new CompteModel();
        $operationModel = new OperationModel();

        $db = db_connect();
        $db->transStart();

        $compteModel->update($compteExpediteurId, [
            'solde' => $compteExpediteur['solde'] - $totalDebit,
        ]);

        $donneesDestinataire = ['solde' => $compteDestinataire['solde'] + $montant];

        if ($fraisRetraitEstime > 0) {
            $donneesDestinataire['credit_frais_retrait'] = (float) ($compteDestinataire['credit_frais_retrait'] ?? 0) + $fraisRetraitEstime;
        }

        $compteModel->update($compteDestinataire['id'], $donneesDestinataire);

        $operationModel->insert([
            'compte_id'              => $compteExpediteurId,
            'compte_destinataire_id' => $compteDestinataire['id'],
            'type_operation_id'      => $resultat['type_transfert_id'],
            'montant'                => $montant,
            'frais'                  => $fraisTransfert + $fraisRetraitEstime,
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/client/transfert')->with('erreur', 'Le transfert a échoué, veuillez réessayer.');
        }

        $message = 'Transfert de ' . number_format($montant, 2, ',', ' ') . ' Ar vers '
            . $numeroDestinataire . ' effectué avec succès.';

        if ($fraisRetraitEstime > 0) {
            $message .= ' Les frais de retrait (' . number_format($fraisRetraitEstime, 2, ',', ' ')
                . ' Ar) ont été pris en charge : le destinataire en bénéficiera lors de son prochain retrait.';
        }

        return redirect()->to('/client/solde')->with('succes', $message);
    }

    /**
     * Affiche le formulaire d'envoi vers plusieurs destinataires.
     */
    public function envoiMultiple()
    {
        return view('client/envoi_multiple');
    }

    /**
     * Vérifie et calcule un envoi groupé vers plusieurs destinataires.
     *
     * Chaque ligne (numéro + montant) est validée individuellement
     * (format, montant, destinataire existant, pas soi-même, pas de
     * doublon, barème disponible). Choix d'équipe assumé : en cas
     * d'erreur sur au moins une ligne, l'envoi groupé est entièrement
     * annulé (aucune ligne n'est exécutée), plutôt que d'envoyer
     * partiellement aux numéros valides.
     *
     * @param list<string> $numeros
     * @param list<string> $montants
     *
     * @return array{erreurs_lignes:list<string>}|array{erreur_globale:string}|array{lignes:list<array>,total_montant:float,total_frais:float,total_debit:float,compte_expediteur:array,type_transfert_id:int}
     */
    private function calculerEnvoiMultiple(
        string $numeroExpediteur,
        int $compteExpediteurId,
        array $numeros,
        array $montants,
        bool $inclureFraisRetrait
    ): array {
        $clientModel        = new ClientModel();
        $compteModel         = new CompteModel();
        $typeOperationModel = new TypeOperationModel();
        $baremeFraisModel    = new BaremeFraisModel();

        $typeTransfert = $typeOperationModel->trouverParCode('transfert');
        $typeRetrait   = $typeOperationModel->trouverParCode('retrait');

        $lignes        = [];
        $erreursLignes = [];
        $numerosVus    = [];

        $nbLignes = max(count($numeros), count($montants));

        for ($i = 0; $i < $nbLignes; $i++) {
            $numero      = trim((string) ($numeros[$i] ?? ''));
            $montantBrut = $montants[$i] ?? '';
            $ligneNo     = $i + 1;

            // Ligne totalement vide (laissée de côté dans la liste dynamique) : ignorée silencieusement.
            if ($numero === '' && ((string) $montantBrut) === '') {
                continue;
            }

            if (! preg_match('/^0[0-9]{9}$/', $numero)) {
                $erreursLignes[] = "Ligne {$ligneNo} : numéro invalide (10 chiffres, doit commencer par 0).";

                continue;
            }

            if (! is_numeric($montantBrut) || (float) $montantBrut <= 0) {
                $erreursLignes[] = "Ligne {$ligneNo} ({$numero}) : montant invalide.";

                continue;
            }

            $montant = (float) $montantBrut;

            if ($numero === $numeroExpediteur) {
                $erreursLignes[] = "Ligne {$ligneNo} : vous ne pouvez pas vous envoyer de l'argent à vous-même.";

                continue;
            }

            if (isset($numerosVus[$numero])) {
                $erreursLignes[] = "Ligne {$ligneNo} : le numéro {$numero} est déjà utilisé à une autre ligne de cet envoi.";

                continue;
            }

            $destinataire = $clientModel->trouverParNumero($numero);

            if ($destinataire === null) {
                $erreursLignes[] = "Ligne {$ligneNo} : le numéro \"{$numero}\" ne correspond à aucun client.";

                continue;
            }

            $trancheTransfert = $baremeFraisModel->trouverTranche($typeTransfert['id'], $montant);

            if ($trancheTransfert === null) {
                $erreursLignes[] = "Ligne {$ligneNo} ({$numero}) : aucun barème de frais ne correspond à ce montant.";

                continue;
            }

            $fraisTransfert     = (float) $trancheTransfert['frais'];
            $fraisRetraitEstime = 0.0;

            if ($inclureFraisRetrait) {
                $trancheRetrait = $baremeFraisModel->trouverTranche($typeRetrait['id'], $montant);

                if ($trancheRetrait === null) {
                    $erreursLignes[] = "Ligne {$ligneNo} ({$numero}) : l'option \"frais de retrait inclus\" est indisponible pour ce montant.";

                    continue;
                }

                $fraisRetraitEstime = (float) $trancheRetrait['frais'];
            }

            $numerosVus[$numero] = true;

            $lignes[] = [
                'numero'               => $numero,
                'montant'              => $montant,
                'destinataire'         => $destinataire,
                'compte_destinataire'  => $compteModel->trouverParClient($destinataire['id']),
                'frais_transfert'      => $fraisTransfert,
                'frais_retrait_estime' => $fraisRetraitEstime,
                'sous_total'           => $montant + $fraisTransfert + $fraisRetraitEstime,
            ];
        }

        if (! empty($erreursLignes)) {
            return ['erreurs_lignes' => $erreursLignes];
        }

        if (empty($lignes)) {
            return ['erreur_globale' => 'Veuillez renseigner au moins un destinataire valide.'];
        }

        $totalMontant = array_sum(array_column($lignes, 'montant'));
        $totalFrais   = array_sum(array_column($lignes, 'frais_transfert')) + array_sum(array_column($lignes, 'frais_retrait_estime'));
        $totalDebit   = $totalMontant + $totalFrais;

        $compteExpediteur = $compteModel->find($compteExpediteurId);

        if ((float) $compteExpediteur['solde'] < $totalDebit) {
            return ['erreur_globale' => 'Solde insuffisant pour cet envoi groupé. Total requis : '
                . number_format($totalDebit, 2, ',', ' ') . ' Ar (solde actuel : '
                . number_format((float) $compteExpediteur['solde'], 2, ',', ' ') . ' Ar).'];
        }

        return [
            'lignes'            => $lignes,
            'total_montant'     => $totalMontant,
            'total_frais'       => $totalFrais,
            'total_debit'       => $totalDebit,
            'compte_expediteur' => $compteExpediteur,
            'type_transfert_id' => $typeTransfert['id'],
        ];
    }

    /**
     * Étape 1 : valide l'envoi groupé et affiche un récapitulatif
     * détaillé (par destinataire) avant toute écriture en base.
     */
    public function envoiMultipleApercu()
    {
        $numeros             = (array) $this->request->getPost('numero_destinataire');
        $montants            = (array) $this->request->getPost('montant');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');

        $resultat = $this->calculerEnvoiMultiple(
            session()->get('numero_telephone'),
            (int) session()->get('compte_id'),
            $numeros,
            $montants,
            $inclureFraisRetrait
        );

        if (isset($resultat['erreurs_lignes'])) {
            return redirect()->back()->withInput()->with('erreurs', $resultat['erreurs_lignes']);
        }

        if (isset($resultat['erreur_globale'])) {
            return redirect()->back()->withInput()->with('erreur', $resultat['erreur_globale']);
        }

        return view('client/envoi_multiple_apercu', [
            'lignes'                => $resultat['lignes'],
            'total_montant'         => $resultat['total_montant'],
            'total_frais'           => $resultat['total_frais'],
            'total_debit'           => $resultat['total_debit'],
            'inclure_frais_retrait' => $inclureFraisRetrait,
        ]);
    }

    /**
     * Étape 2 : recalcule tout depuis la base (jamais confiance aux
     * valeurs soumises) et exécute l'envoi groupé dans une transaction
     * unique : un seul débit chez l'expéditeur pour le total, un crédit
     * (+ crédit de frais de retrait éventuel) par destinataire, et une
     * opération enregistrée par destinataire. Tout échec annule
     * l'intégralité de l'envoi (aucune écriture partielle).
     */
    public function envoiMultipleConfirmer()
    {
        $numeros             = (array) $this->request->getPost('numero_destinataire');
        $montants            = (array) $this->request->getPost('montant');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');
        $compteExpediteurId  = (int) session()->get('compte_id');

        $resultat = $this->calculerEnvoiMultiple(
            session()->get('numero_telephone'),
            $compteExpediteurId,
            $numeros,
            $montants,
            $inclureFraisRetrait
        );

        if (isset($resultat['erreurs_lignes']) || isset($resultat['erreur_globale'])) {
            $erreur = $resultat['erreur_globale'] ?? "Une erreur est survenue, veuillez recommencer votre envoi.";

            return redirect()->to('/client/envoi-multiple')->with('erreur', $erreur);
        }

        $compteModel    = new CompteModel();
        $operationModel = new OperationModel();

        $db = db_connect();
        $db->transStart();

        $compteExpediteur = $resultat['compte_expediteur'];

        $compteModel->update($compteExpediteurId, [
            'solde' => $compteExpediteur['solde'] - $resultat['total_debit'],
        ]);

        foreach ($resultat['lignes'] as $ligne) {
            $compteDestinataire = $ligne['compte_destinataire'];

            $donneesDestinataire = ['solde' => $compteDestinataire['solde'] + $ligne['montant']];

            if ($ligne['frais_retrait_estime'] > 0) {
                $donneesDestinataire['credit_frais_retrait'] = (float) ($compteDestinataire['credit_frais_retrait'] ?? 0) + $ligne['frais_retrait_estime'];
            }

            $compteModel->update($compteDestinataire['id'], $donneesDestinataire);

            $operationModel->insert([
                'compte_id'              => $compteExpediteurId,
                'compte_destinataire_id' => $compteDestinataire['id'],
                'type_operation_id'      => $resultat['type_transfert_id'],
                'montant'                => $ligne['montant'],
                'frais'                  => $ligne['frais_transfert'] + $ligne['frais_retrait_estime'],
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/client/envoi-multiple')->with('erreur', "L'envoi groupé a échoué, veuillez réessayer. Aucun montant n'a été débité.");
        }

        return redirect()->to('/client/solde')->with('succes', 'Envoi groupé effectué avec succès vers '
            . count($resultat['lignes']) . ' destinataire(s), pour un total débité de '
            . number_format($resultat['total_debit'], 2, ',', ' ') . ' Ar.');
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

<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\PrefixeOperateurModel;

/**
 * Gère la connexion automatique des clients par numéro de téléphone.
 *
 * - Si le numéro n'existe pas encore mais que son préfixe est valide,
 *   un compte client est créé automatiquement (solde initial 0).
 * - Si le numéro existe déjà, le client est simplement reconnecté sur
 *   son compte existant.
 */
class Auth extends BaseController
{
    /**
     * Affiche le formulaire de connexion par numéro de téléphone.
     */
    public function index()
    {
        // Si un client est déjà connecté, on l'envoie directement sur son espace.
        if (session()->get('isClientLoggedIn')) {
            return redirect()->to('/client/tableau-de-bord');
        }

        return view('auth/login');
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login()
    {
        $rules = [
            'numero_telephone' => [
                'label' => 'Numéro de téléphone',
                'rules' => 'required|regex_match[/^0[0-9]{9}$/]',
                'errors' => [
                    'required'    => 'Veuillez saisir un numéro de téléphone.',
                    'regex_match' => 'Le numéro doit être composé de 10 chiffres et commencer par 0.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('erreurs', $this->validator->getErrors());
        }

        $numeroTelephone = $this->request->getPost('numero_telephone');
        $prefixe         = substr($numeroTelephone, 0, 3);

        $prefixeModel     = new PrefixeOperateurModel();
        $prefixeOperateur = $prefixeModel->where('prefixe', $prefixe)->first();

        if ($prefixeOperateur === null) {
            return redirect()->back()->withInput()->with('erreur', "Le préfixe \"{$prefixe}\" n'est pas reconnu par l'opérateur.");
        }

        $clientModel = new ClientModel();
        $compteModel = new CompteModel();

        $client = $clientModel->trouverParNumero($numeroTelephone);

        if ($client === null) {
            // Nouveau client : création du compte à la volée.
            $clientId = $clientModel->insert([
                'numero_telephone' => $numeroTelephone,
                'prefixe_id'       => $prefixeOperateur['id'],
            ], true);

            $compteModel->insert([
                'client_id' => $clientId,
                'solde'     => 0,
            ]);

            $client = $clientModel->find($clientId);
        }

        $compte = $compteModel->trouverParClient($client['id']);

        session()->set([
            'isClientLoggedIn' => true,
            'client_id'        => $client['id'],
            'compte_id'        => $compte['id'],
            'numero_telephone' => $client['numero_telephone'],
        ]);

        return redirect()->to('/client/tableau-de-bord');
    }

    /**
     * Déconnecte le client et détruit sa session.
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/connexion');
    }
}

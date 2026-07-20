<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OperateurModel;

/**
 * Authentification côté opérateur (back-office).
 */
class AuthController extends BaseController
{
    protected OperateurModel $operateurModel;

    public function __construct()
    {
        $this->operateurModel = new OperateurModel();
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLogin()
    {
        if (session()->get('operateur_id')) {
            return redirect()->to('/admin');
        }

        return view('admin/auth/login');
    }

    /**
     * Traite la soumission du formulaire de connexion.
     */
    public function login()
    {
        $login      = trim((string) $this->request->getPost('login'));
        $motDePasse = (string) $this->request->getPost('mot_de_passe');

        $operateur = $this->operateurModel->verifierIdentifiants($login, $motDePasse);

        if ($operateur === null) {
            return redirect()->to('/admin/login')
                ->withInput()
                ->with('errors', ['Identifiant ou mot de passe incorrect.']);
        }

        session()->regenerate();
        session()->set([
            'operateur_id'    => $operateur['id'],
            'operateur_login' => $operateur['login'],
        ]);

        return redirect()->to('/admin')->with('success', 'Connexion réussie. Bienvenue ' . $operateur['login'] . ' !');
    }

    /**
     * Déconnecte l'opérateur et détruit la session.
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/admin/login')->with('success', 'Vous avez été déconnecté.');
    }
}

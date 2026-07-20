<?php

namespace App\Controllers;

class Home extends BaseController
{
    /**
     * Page d'accueil : choix entre l'espace opérateur (back-office) et
     * l'espace client (dépôt, retrait, transfert...).
     */
    public function index(): string
    {
        return view('landing/index');
    }
}

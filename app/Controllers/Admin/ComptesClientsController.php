<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientModel;

/**
 * Côté opérateur (back-office) — Situation des comptes clients.
 */
class ComptesClientsController extends BaseController
{
    protected ClientModel $clientModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }

    public function index()
    {
        $recherche = trim((string) $this->request->getGet('recherche'));
        $recherche = $recherche === '' ? null : $recherche;

        $clients = $this->clientModel->listeAvecSolde($recherche);

        $totalSoldes = 0.0;
        foreach ($clients as $client) {
            $totalSoldes += (float) $client['solde'];
        }

        return view('admin/comptes_clients/index', [
            'clients'     => $clients,
            'recherche'   => $recherche,
            'totalSoldes' => $totalSoldes,
        ]);
    }
}

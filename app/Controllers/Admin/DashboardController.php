<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\OperationModel;

/**
 * Côté opérateur (back-office) — Tableau de bord.
 */
class DashboardController extends BaseController
{
    protected ClientModel $clientModel;
    protected OperationModel $operationModel;

    public function __construct()
    {
        $this->clientModel    = new ClientModel();
        $this->operationModel = new OperationModel();
    }

    public function index()
    {
        $clients = $this->clientModel->listeAvecSolde();

        $totalSoldes = 0.0;
        foreach ($clients as $client) {
            $totalSoldes += (float) $client['solde'];
        }

        $recap = $this->operationModel->recapFraisParType();

        $totalGains     = 0.0;
        $totalOperations = 0;
        foreach ($recap as $ligne) {
            $totalGains      += (float) $ligne['total_frais'];
            $totalOperations += (int) $ligne['nb_operations'];
        }

        return view('admin/dashboard/index', [
            'title'            => 'Tableau de bord',
            'subtitle'         => "Vue d'ensemble de l'activité du réseau.",
            'totalClients'     => count($clients),
            'totalSoldes'      => $totalSoldes,
            'totalGains'       => $totalGains,
            'totalOperations'  => $totalOperations,
            'recap'            => $recap,
            'recentOperations' => $this->operationModel->recentWithDetails(8),
        ]);
    }
}

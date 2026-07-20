<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OperationModel;
use App\Models\TypeOperationModel;

/**
 * Côté opérateur (back-office) — Situation des gains (frais perçus).
 */
class GainsController extends BaseController
{
    protected OperationModel $operationModel;
    protected TypeOperationModel $typeModel;

    public function __construct()
    {
        $this->operationModel = new OperationModel();
        $this->typeModel      = new TypeOperationModel();
    }

    public function index()
    {
        $typeOperationId = $this->request->getGet('type_operation_id');
        $typeOperationId = ($typeOperationId === null || $typeOperationId === '') ? null : (int) $typeOperationId;

        // Bloc 1 — gains internes : frais habituels du barème, tous types
        // d'opération confondus (dépôt, retrait, transfert), qu'ils soient
        // échangés entre clients internes ou vers un numéro externe.
        $recapInterne = $this->operationModel->recapFraisParType($typeOperationId);

        $totalInterne = 0.0;
        foreach ($recapInterne as $ligne) {
            $totalInterne += (float) $ligne['total_frais'];
        }

        // Bloc 2 — gains "autres opérateurs" : commission inter-opérateur
        // perçue uniquement sur les transferts sortants vers un préfixe
        // externe, distincte des frais internes classiques ci-dessus.
        $recapExterne = $this->operationModel->recapCommissionParOperateurExterne();

        $totalExterne = 0.0;
        foreach ($recapExterne as $ligne) {
            $totalExterne += (float) $ligne['total_commission'];
        }

        return view('admin/gains/index', [
            'title'           => 'Situation des gains',
            'subtitle'        => "Revenus générés par les frais d'opérations, avec le détail de la commission inter-opérateur.",
            'types'           => $this->typeModel->orderBy('id', 'ASC')->findAll(),
            'recapInterne'    => $recapInterne,
            'recapExterne'    => $recapExterne,
            'totalInterne'    => $totalInterne,
            'totalExterne'    => $totalExterne,
            'totalGlobal'     => $totalInterne + $totalExterne,
            'typeOperationId' => $typeOperationId,
        ]);
    }
}

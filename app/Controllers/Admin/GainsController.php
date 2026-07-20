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

        $recap = $this->operationModel->recapFraisParType($typeOperationId);

        $totalGlobal = 0.0;
        foreach ($recap as $ligne) {
            $totalGlobal += (float) $ligne['total_frais'];
        }

        return view('admin/gains/index', [
            'title'           => 'Situation des gains',
            'subtitle'        => "Revenus générés par les frais d'opérations (retraits et transferts).",
            'types'           => $this->typeModel->orderBy('id', 'ASC')->findAll(),
            'recap'           => $recap,
            'totalGlobal'     => $totalGlobal,
            'typeOperationId' => $typeOperationId,
        ]);
    }
}

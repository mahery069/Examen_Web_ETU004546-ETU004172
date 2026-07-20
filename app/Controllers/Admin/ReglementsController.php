<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OperationModel;
use App\Models\PrefixeOperateurModel;

/**
 * Côté opérateur (back-office) — Situation des montants à envoyer à
 * chaque opérateur externe (règlement inter-opérateurs).
 */
class ReglementsController extends BaseController
{
    protected OperationModel $operationModel;
    protected PrefixeOperateurModel $prefixeModel;

    public function __construct()
    {
        $this->operationModel = new OperationModel();
        $this->prefixeModel   = new PrefixeOperateurModel();
    }

    public function index()
    {
        $prefixeExterneId = $this->request->getGet('prefixe_id');
        $prefixeExterneId = ($prefixeExterneId === null || $prefixeExterneId === '') ? null : (int) $prefixeExterneId;

        $recap = $this->operationModel->recapMontantsDusParOperateurExterne();

        $totalMontantDu    = 0.0;
        $totalTransferts   = 0;
        foreach ($recap as $ligne) {
            $totalMontantDu  += (float) $ligne['montant_du'];
            $totalTransferts += (int) $ligne['nb_transferts'];
        }

        $detail = $this->operationModel->detailTransfertsExternes($prefixeExterneId);

        return view('admin/reglements/index', [
            'title'            => 'Règlements inter-opérateurs',
            'subtitle'         => "Montants nets à reverser aux autres opérateurs pour les transferts sortants vers leurs clients.",
            'operateursExternes' => $this->prefixeModel->where('is_internal', 0)->orderBy('prefixe', 'ASC')->findAll(),
            'recap'            => $recap,
            'detail'           => $detail,
            'totalMontantDu'   => $totalMontantDu,
            'totalTransferts'  => $totalTransferts,
            'prefixeExterneId' => $prefixeExterneId,
        ]);
    }
}

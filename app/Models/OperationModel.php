<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationModel extends Model
{
    protected $table         = 'operations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'compte_id',
        'compte_destinataire_id',
        'type_operation_id',
        'montant',
        'frais',
    ];

    /**
     * Historique des opérations d'un compte (envoyées et reçues),
     * du plus récent au plus ancien.
     */
    public function historiqueDuCompte(int $compteId)
    {
        return $this->groupStart()
            ->where('compte_id', $compteId)
            ->orWhere('compte_destinataire_id', $compteId)
            ->groupEnd()
            ->orderBy('date_operation', 'DESC')
            ->findAll();
    }
}

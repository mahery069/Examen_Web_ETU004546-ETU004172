<?php

namespace App\Models;

use CodeIgniter\Model;

class BaremeFraisModel extends Model
{
    protected $table         = 'baremes_frais';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['type_operation_id', 'montant_min', 'montant_max', 'frais'];

    /**
     * Recherche la tranche de frais correspondant à un montant, pour un
     * type d'opération donné (ex : retrait, transfert).
     */
    public function trouverTranche(int $typeOperationId, float $montant): ?array
    {
        return $this->where('type_operation_id', $typeOperationId)
            ->where('montant_min <=', $montant)
            ->where('montant_max >=', $montant)
            ->first();
    }
}

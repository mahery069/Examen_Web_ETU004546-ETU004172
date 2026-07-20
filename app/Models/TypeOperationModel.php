<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperationModel extends Model
{
    protected $table         = 'types_operation';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['code', 'libelle'];

    /**
     * Recherche un type d'opération par son code ('depot', 'retrait', 'transfert').
     */
    public function trouverParCode(string $code): ?array
    {
        return $this->where('code', $code)->first();
    }
}

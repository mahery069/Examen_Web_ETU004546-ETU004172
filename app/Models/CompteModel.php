<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteModel extends Model
{
    protected $table         = 'comptes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['client_id', 'solde', 'credit_frais_retrait'];

    /**
     * Récupère le compte associé à un client.
     */
    public function trouverParClient(int $clientId): ?array
    {
        return $this->where('client_id', $clientId)->first();
    }
}

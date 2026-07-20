<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table         = 'clients';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['numero_telephone', 'prefixe_id'];

    /**
     * Recherche un client par son numéro de téléphone.
     */
    public function trouverParNumero(string $numeroTelephone): ?array
    {
        return $this->where('numero_telephone', $numeroTelephone)->first();
    }
}

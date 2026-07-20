<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table         = 'operateurs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['login', 'mot_de_passe'];

    /**
     * Vérifie les identifiants et renvoie l'opérateur si valides, sinon null.
     *
     * @return array<string, mixed>|null
     */
    public function verifierIdentifiants(string $login, string $motDePasse): ?array
    {
        $operateur = $this->where('login', $login)->first();

        if ($operateur === null) {
            return null;
        }

        if (! password_verify($motDePasse, $operateur['mot_de_passe'])) {
            return null;
        }

        return $operateur;
    }
}

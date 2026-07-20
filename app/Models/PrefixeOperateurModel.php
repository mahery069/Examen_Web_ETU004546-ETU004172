<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeOperateurModel extends Model
{
    protected $table            = 'prefixes_operateur';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $allowedFields    = ['prefixe', 'libelle'];

    /**
     * Vérifie si un préfixe (les 3 premiers chiffres d'un numéro) est
     * référencé comme préfixe opérateur valide.
     */
    public function prefixeExiste(string $prefixe): bool
    {
        return $this->where('prefixe', $prefixe)->first() !== null;
    }
}

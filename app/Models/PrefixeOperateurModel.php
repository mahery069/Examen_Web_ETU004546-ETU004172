<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeOperateurModel extends Model
{
    protected $table            = 'prefixes_operateur';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['prefixe', 'libelle'];

    protected $validationRules = [
        'id'      => 'permit_empty|is_natural_no_zero',
        'prefixe' => 'required|regex_match[/^[0-9]{3}$/]|is_unique[prefixes_operateur.prefixe,id,{id}]',
        'libelle' => 'permit_empty|max_length[50]',
    ];

    protected $validationMessages = [
        'prefixe' => [
            'required'    => 'Le préfixe est obligatoire.',
            'regex_match' => 'Le préfixe doit être composé exactement de 3 chiffres (ex: 033).',
            'is_unique'   => 'Ce préfixe existe déjà.',
        ],
        'libelle' => [
            'max_length' => 'Le libellé ne doit pas dépasser 50 caractères.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Vérifie si un préfixe (les 3 premiers chiffres d'un numéro) est
     * référencé comme préfixe opérateur valide.
     */
    public function prefixeExiste(string $prefixe): bool
    {
        return $this->where('prefixe', $prefixe)->first() !== null;
    }
}

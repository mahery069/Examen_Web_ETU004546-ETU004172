<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeOperateurModel extends Model
{
    protected $table            = 'prefixes_operateur';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['prefixe', 'libelle', 'is_internal', 'commission_pourcentage'];

    protected $validationRules = [
        'id'      => 'permit_empty|is_natural_no_zero',
        'prefixe' => 'required|regex_match[/^[0-9]{3}$/]|is_unique[prefixes_operateur.prefixe,id,{id}]',
        'libelle' => 'permit_empty|max_length[50]',
        'is_internal' => 'permit_empty|in_list[0,1]',
        'commission_pourcentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
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
        'commission_pourcentage' => [
            'decimal'                => 'La commission doit être un nombre.',
            'greater_than_equal_to'  => 'La commission doit être comprise entre 0 et 100.',
            'less_than_equal_to'     => 'La commission doit être comprise entre 0 et 100.',
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

    /**
     * Calcule le montant de la commission inter-opérateur pour un transfert
     * de `$montant` sortant vers le préfixe identifié par `$prefixeId`.
     *
     * Retourne 0 si le préfixe est interne (notre opérateur), introuvable,
     * ou si aucun pourcentage n'est configuré.
     */
    public function calculerCommission(?int $prefixeId, float $montant): float
    {
        if ($prefixeId === null) {
            return 0.0;
        }

        $prefixe = $this->find($prefixeId);

        if ($prefixe === null || (bool) $prefixe['is_internal']) {
            return 0.0;
        }

        return round($montant * ((float) $prefixe['commission_pourcentage'] / 100), 2);
    }
}

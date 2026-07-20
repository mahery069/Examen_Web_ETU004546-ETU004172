<?php

namespace App\Models;

use CodeIgniter\Model;

class BaremeFraisModel extends Model
{
    protected $table            = 'baremes_frais';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['type_operation_id', 'montant_min', 'montant_max', 'frais'];

    protected $validationRules = [
        'id'                => 'permit_empty|is_natural_no_zero',
        'type_operation_id' => 'required|is_natural_no_zero',
        'montant_min'       => 'required|decimal|greater_than_equal_to[0]',
        'montant_max'       => 'required|decimal|greater_than_equal_to[{montant_min}]',
        'frais'             => 'required|decimal|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'type_operation_id' => [
            'required'       => "Le type d'opération est obligatoire.",
            'is_natural_no_zero' => "Le type d'opération sélectionné est invalide.",
        ],
        'montant_min' => [
            'required'             => 'Le montant minimum est obligatoire.',
            'decimal'              => 'Le montant minimum doit être un nombre.',
            'greater_than_equal_to' => 'Le montant minimum doit être positif ou nul.',
        ],
        'montant_max' => [
            'required'             => 'Le montant maximum est obligatoire.',
            'decimal'              => 'Le montant maximum doit être un nombre.',
            'greater_than_equal_to' => 'Le montant maximum doit être supérieur ou égal au montant minimum.',
        ],
        'frais' => [
            'required'             => 'Le montant des frais est obligatoire.',
            'decimal'              => 'Le montant des frais doit être un nombre.',
            'greater_than_equal_to' => 'Le montant des frais doit être positif ou nul.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Recherche les tranches existantes du même type d'opération qui
     * chevauchent l'intervalle [$min, $max]. Permet d'exclure une tranche
     * (celle en cours de modification) de la recherche.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findOverlapping(int $typeOperationId, float $min, float $max, ?int $excludeId = null): array
    {
        $builder = $this->where('type_operation_id', $typeOperationId)
            ->where('montant_min <=', $max)
            ->where('montant_max >=', $min);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->findAll();
    }

    /**
     * Retourne les tranches d'un type d'opération, triées par montant_min,
     * ainsi que la liste des "trous" détectés entre deux tranches consécutives.
     *
     * @return array{tranches: array<int, array<string, mixed>>, gaps: array<int, array<string, float>>}
     */
    public function getTranchesAvecTrous(int $typeOperationId): array
    {
        $tranches = $this->where('type_operation_id', $typeOperationId)
            ->orderBy('montant_min', 'ASC')
            ->findAll();

        $gaps = [];

        for ($i = 0, $count = count($tranches); $i < $count - 1; $i++) {
            $finCourant  = (float) $tranches[$i]['montant_max'];
            $debutSuivant = (float) $tranches[$i + 1]['montant_min'];

            if ($debutSuivant > $finCourant + 1) {
                $gaps[] = [
                    'apres_tranche_index' => $i,
                    'min'                 => $finCourant + 1,
                    'max'                 => $debutSuivant - 1,
                ];
            }
        }

        return ['tranches' => $tranches, 'gaps' => $gaps];
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

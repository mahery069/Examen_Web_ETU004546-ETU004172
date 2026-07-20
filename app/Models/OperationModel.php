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
     * Récapitulatif des frais perçus, groupés par type d'opération.
     * Chaque type d'opération apparaît toujours dans le résultat (même à 0),
     * pour permettre un tableau récapitulatif complet.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recapFraisParType(?int $typeOperationId = null): array
    {
        $builder = $this->db->table('types_operation t')
            ->select('t.id AS type_operation_id, t.code, t.libelle, ' .
                'COUNT(o.id) AS nb_operations, COALESCE(SUM(o.frais), 0) AS total_frais')
            ->join('operations o', 'o.type_operation_id = t.id', 'left')
            ->groupBy('t.id, t.code, t.libelle')
            ->orderBy('t.id', 'ASC');

        if ($typeOperationId !== null) {
            $builder->where('t.id', $typeOperationId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Dernières opérations, avec le libellé du type et le numéro de
     * téléphone du titulaire du compte source. Utilisé pour le tableau
     * de bord opérateur.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recentWithDetails(int $limit = 5): array
    {
        return $this->db->table('operations o')
            ->select('o.id, o.montant, o.frais, o.date_operation, t.code, t.libelle, c.numero_telephone')
            ->join('types_operation t', 't.id = o.type_operation_id')
            ->join('comptes cpt', 'cpt.id = o.compte_id')
            ->join('clients c', 'c.id = cpt.client_id')
            ->orderBy('o.date_operation', 'DESC')
            ->orderBy('o.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}

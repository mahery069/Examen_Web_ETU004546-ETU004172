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
     * Historique des opérations d'un compte (envoyées et reçues),
     * du plus récent au plus ancien. Chaque ligne est enrichie avec le
     * libellé du type d'opération ainsi que les numéros de téléphone de
     * l'expéditeur et, le cas échéant, du destinataire.
     */
    public function historiqueDuCompte(int $compteId, ?int $limite = null): array
    {
        $builder = $this->db->table('operations o')
            ->select('o.id, o.montant, o.frais, o.date_operation, '
                . 'o.compte_id, o.compte_destinataire_id, '
                . 't.code AS type_code, t.libelle AS type_libelle, '
                . 'ce.numero_telephone AS numero_expediteur, '
                . 'cd.numero_telephone AS numero_destinataire')
            ->join('types_operation t', 't.id = o.type_operation_id')
            ->join('comptes co_exp', 'co_exp.id = o.compte_id')
            ->join('clients ce', 'ce.id = co_exp.client_id')
            ->join('comptes co_dest', 'co_dest.id = o.compte_destinataire_id', 'left')
            ->join('clients cd', 'cd.id = co_dest.client_id', 'left')
            ->groupStart()
                ->where('o.compte_id', $compteId)
                ->orWhere('o.compte_destinataire_id', $compteId)
            ->groupEnd()
            ->orderBy('o.date_operation', 'DESC');

        if ($limite !== null) {
            $builder->limit($limite);
        }

        return $builder->get()->getResultArray();
    }
}

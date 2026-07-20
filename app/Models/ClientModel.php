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
     * Liste des clients avec leur solde actuel, avec une recherche
     * optionnelle (partielle) sur le numéro de téléphone.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listeAvecSolde(?string $recherche = null): array
    {
        $builder = $this->db->table('clients c')
            ->select('c.id, c.numero_telephone, c.date_creation, ' .
                'COALESCE(cpt.solde, 0) AS solde')
            ->join('comptes cpt', 'cpt.client_id = c.id', 'left')
            ->orderBy('c.numero_telephone', 'ASC');

        if ($recherche !== null && $recherche !== '') {
            $builder->like('c.numero_telephone', $recherche);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Recherche un client par son numéro de téléphone.
     */
    public function trouverParNumero(string $numeroTelephone): ?array
    {
        return $this->where('numero_telephone', $numeroTelephone)->first();
    }
}

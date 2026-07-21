<?php
namespace App\Models;

use CodeIgniter\Model;


class PromotionFraisModel extends Model
{
    protected $table = "promotion_frais";
    protected $primaryKey = "id";
    protected $returnType = "array";
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nom',
        'type_operation_id',
        'pourcentage_remise',
        'actif',
        ' date_debut',
        'date_fin',
    ];

    protected $validationMessages=[
        'nom'=> [
            'required'=> '',
            'max_length'
        ]
    ]
}





?>
<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BaremeFraisModel;
use App\Models\TypeOperationModel;

/**
 * Côté opérateur (back-office) — Gestion des barèmes de frais par tranche
 * de montant, pour chaque type d'opération (dépôt / retrait / transfert).
 */
class BaremesFraisController extends BaseController
{
    protected BaremeFraisModel $baremeModel;
    protected TypeOperationModel $typeModel;

    public function __construct()
    {
        $this->baremeModel = new BaremeFraisModel();
        $this->typeModel   = new TypeOperationModel();
    }

    /**
     * Liste des tranches, groupées par type d'opération, + formulaire d'ajout.
     */
    public function index()
    {
        $types = $this->typeModel->orderBy('id', 'ASC')->findAll();

        $groupes = [];
        foreach ($types as $type) {
            $groupes[] = [
                'type'     => $type,
                'tranches' => $this->baremeModel->getTranchesAvecTrous((int) $type['id']),
            ];
        }

        return view('admin/baremes/index', [
            'title'    => 'Barèmes de frais',
            'subtitle' => "Configurez les frais par tranche de montant pour chaque type d'opération.",
            'groupes'  => $groupes,
            'types'    => $types,
        ]);
    }

    /**
     * Enregistre une nouvelle tranche de frais.
     */
    public function store()
    {
        $data = $this->collectInput();

        return $this->saveTranche($data, redirect()->back()->withInput());
    }

    /**
     * Met à jour une tranche de frais existante.
     */
    public function update($id = null)
    {
        $tranche = $this->baremeModel->find($id);

        if ($tranche === null) {
            return redirect()->to('/admin/baremes')->with('errors', ['Tranche introuvable.']);
        }

        $data       = $this->collectInput();
        $data['id'] = $id;

        return $this->saveTranche($data, redirect()->back()->withInput()->with('edit_id', (int) $id), (int) $id);
    }

    /**
     * Supprime une tranche de frais.
     */
    public function delete($id = null)
    {
        $tranche = $this->baremeModel->find($id);

        if ($tranche === null) {
            return redirect()->to('/admin/baremes')->with('errors', ['Tranche introuvable.']);
        }

        $this->baremeModel->delete($id);

        return redirect()->to('/admin/baremes')
            ->with('success', 'Tranche supprimée avec succès.');
    }

    /**
     * Récupère et normalise les données du formulaire.
     *
     * @return array<string, mixed>
     */
    private function collectInput(): array
    {
        return [
            'type_operation_id' => (int) $this->request->getPost('type_operation_id'),
            'montant_min'       => trim((string) $this->request->getPost('montant_min')),
            'montant_max'       => trim((string) $this->request->getPost('montant_max')),
            'frais'             => trim((string) $this->request->getPost('frais')),
        ];
    }

    /**
     * Valide, vérifie le chevauchement, puis enregistre la tranche.
     */
    private function saveTranche(array $data, $failureRedirect, ?int $excludeId = null)
    {
        if (! $this->baremeModel->validate($data)) {
            return $failureRedirect->with('errors', $this->baremeModel->errors());
        }

        $overlaps = $this->baremeModel->findOverlapping(
            $data['type_operation_id'],
            (float) $data['montant_min'],
            (float) $data['montant_max'],
            $excludeId
        );

        if (! empty($overlaps)) {
            $chevauchements = array_map(
                static fn ($t) => $t['montant_min'] . ' - ' . $t['montant_max'],
                $overlaps
            );

            return $failureRedirect->with('errors', [
                'Cette tranche chevauche une tranche existante (' . implode(', ', $chevauchements) . ') pour ce type d\'opération.',
            ]);
        }

        $this->baremeModel->save($data);

        return redirect()->to('/admin/baremes')
            ->with('success', 'Tranche enregistrée avec succès.');
    }
}

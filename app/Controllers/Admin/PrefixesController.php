<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PrefixeOperateurModel;

/**
 * Côté opérateur (back-office) — Configuration des préfixes valables.
 */
class PrefixesController extends BaseController
{
    protected PrefixeOperateurModel $prefixeModel;

    public function __construct()
    {
        $this->prefixeModel = new PrefixeOperateurModel();
    }

    /**
     * Liste des préfixes + formulaire d'ajout.
     */
    public function index()
    {
        $data = [
            'title'    => 'Préfixes réseau',
            'subtitle' => "Définissez les préfixes de numéros valables pour l'inscription et les opérations.",
            'prefixes' => $this->prefixeModel->orderBy('prefixe', 'ASC')->findAll(),
        ];

        return view('admin/prefixes/index', $data);
    }

    /**
     * Enregistre un nouveau préfixe.
     */
    public function store()
    {
        $data = [
            'prefixe' => trim((string) $this->request->getPost('prefixe')),
            'libelle' => trim((string) $this->request->getPost('libelle')),
        ];

        if (! $this->prefixeModel->save($data)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->prefixeModel->errors())
                ->with('old_action', 'create');
        }

        return redirect()->to('/admin/prefixes')
            ->with('success', 'Préfixe "' . $data['prefixe'] . '" ajouté avec succès.');
    }

    /**
     * Met à jour un préfixe existant.
     */
    public function update($id = null)
    {
        $prefixe = $this->prefixeModel->find($id);

        if ($prefixe === null) {
            return redirect()->to('/admin/prefixes')->with('errors', ['Préfixe introuvable.']);
        }

        $data = [
            'id'      => $id,
            'prefixe' => trim((string) $this->request->getPost('prefixe')),
            'libelle' => trim((string) $this->request->getPost('libelle')),
        ];

        if (! $this->prefixeModel->save($data)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->prefixeModel->errors())
                ->with('old_action', 'edit')
                ->with('edit_id', (int) $id);
        }

        return redirect()->to('/admin/prefixes')
            ->with('success', 'Préfixe "' . $data['prefixe'] . '" modifié avec succès.');
    }

    /**
     * Supprime un préfixe.
     */
    public function delete($id = null)
    {
        $prefixe = $this->prefixeModel->find($id);

        if ($prefixe === null) {
            return redirect()->to('/admin/prefixes')->with('errors', ['Préfixe introuvable.']);
        }

        try {
            $this->prefixeModel->delete($id);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/prefixes')
                ->with('errors', ['Impossible de supprimer ce préfixe : il est déjà utilisé par au moins un client.']);
        }

        return redirect()->to('/admin/prefixes')
            ->with('success', 'Préfixe "' . $prefixe['prefixe'] . '" supprimé avec succès.');
    }
}

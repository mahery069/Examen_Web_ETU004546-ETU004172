<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Protège les pages du back-office opérateur : redirige vers la page de
 * connexion si aucun opérateur n'est authentifié en session.
 */
class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('operateur_id')) {
            return redirect()->to('/admin/login')
                ->with('errors', ['Veuillez vous connecter pour accéder au back-office.']);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien à faire après la requête.
    }
}

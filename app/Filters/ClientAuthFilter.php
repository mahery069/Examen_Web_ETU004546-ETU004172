<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Protège les routes réservées aux clients connectés.
 * Redirige vers le formulaire de connexion si aucune session client active.
 */
class ClientAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isClientLoggedIn')) {
            return redirect()->to('/connexion')->with('erreur', 'Veuillez vous connecter.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien à faire après la requête.
    }
}

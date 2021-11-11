<?php 

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{

    /**
     * 
     * Cette , va nous permettre de récupéré les information a travers un cookies
     * Donc, il ne va pas renvoyer un token
     * 
     * @Route("/api/login", name="api_login", methods="POST")
     *
     * @return void
     */
    public function login() 
    {
        $user = $this->getUser();
        return $this->json([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ]);
    }
}
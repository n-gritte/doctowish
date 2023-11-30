<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        $user = $this->getUser();
        // if (!$user) {
        //     $this->addFlash('error', 'Une erreur est parvenue');
        //     return $this->redirectToRoute('app_home');
        // }
        
        // dd($user);
        return $this->render('user/index.html.twig', 
            [
                'controller_name' => 'UserController',
                'user' => $user,
            ]
        );
    }
}

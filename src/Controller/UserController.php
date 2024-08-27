<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('user/userProfile.html.twig', [
            'user' => $user,
        ]);
    }
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/userPosts', name: 'user_posts')]
    public function userPosts(): Response
    {
        $user = $this->getUser(); // Obtiene el usuario actualmente autenticado

    if (!$user) {
        throw $this->createNotFoundException('User not found');
    }

    $userPosts = $user->getPosts(); // Obtiene los posts relacionados con el usuario
        return $this->render('user/userPosts.html.twig', [
            'posts' => $userPosts,
            'user' => $user,

        ]);
    }
}

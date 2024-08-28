<?php

namespace App\Controller;

use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserProfileType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $this->em->flush();

            return $this->redirectToRoute('app_profile');

        }
        return $this->render('user/userProfile.html.twig', [
            'user' => $user,
            'form' => $form->createView()
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

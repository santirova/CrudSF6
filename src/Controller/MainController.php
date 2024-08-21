<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        $posts = $this->em->getRepository(Post::class)->findAll();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'posts' => $posts
        ]);
    }

    #[Route('/create_post', name: 'create_post')]
    public function createPost(Request $request){
        $post = new Post();
        $form = $this-> createForm(PostType::class,$post);
        $form -> handleRequest($request);

        if ($form->isSubmitted() && $form ->isValid()) {
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('message','Inserted Successfully');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/post.html.twig',[
            'form' => $form -> createView()
        ]);
    }

    #[Route('/edit_post/{id}', name: 'edit_post')]
    public function editPost(Request $request, $id)
    {
        $post = $this->em->getRepository(Post::class)->find($id);
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form ->isValid()) {
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('message','Updated Successfully');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/post.html.twig',[
            'form' => $form->createView()        
        ]);
    }

    #[Route('/delete_post/{id}', name: 'delete_post')]
    public function deletePost( $id)
    {
        $post = $this->em->getRepository(Post::class)->find($id);
        $this->em->remove($post);
        $this->em->flush();

        $this->addFlash('message', 'Deleted Successfully');
        return $this->redirectToRoute('app_main');
    }
}

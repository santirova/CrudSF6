<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\FilterPostsType;
use App\Form\PostType;
use App\Service\FileUploader;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends AbstractController
{
    private $em;
    private $date;

    public function __construct(EntityManagerInterface $em)
    {
        $this->date = new DateTime();
        $this->date->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));
        $this->em = $em;
    }


    #[Route('/main', name: 'app_main')]
    public function index(Request $request, PaginatorInterface $paginator, SessionInterface $session): Response
    {
        $filterForm = $this->createForm(FilterPostsType::class);
        $filterForm->handleRequest($request);

        $queryBuilder = $this->em->getRepository(Post::class)->createQueryBuilder('p');
        $filtersApplied = false;

        // Cargar los filtros de la sesión
        $startDate = $session->get('startDate');
        $endDate = $session->get('endDate');
        $type = $session->get('type');

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $startDate = $filterForm->get('startDate')->getData();
            $endDate = $filterForm->get('endDate')->getData();
            $type = $filterForm->get('type')->getData();

            // Guardar los filtros en la sesión
            $session->set('startDate', $startDate);
            $session->set('endDate', $endDate);
            $session->set('type', $type);

            $filtersApplied = true;

            // Reiniciar la paginación a la página 1 cuando se aplican filtros
            return $this->redirectToRoute('app_main', ['page' => 1]);
        } elseif ($startDate || $endDate || $type) {
            $filtersApplied = true;
        }

        // Aplicar los filtros a la consulta
        if ($startDate) {
            $queryBuilder->andWhere('p.creation_date >= :startDate')
                        ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $queryBuilder->andWhere('p.creation_date <= :endDate')
                        ->setParameter('endDate', $endDate);
        }

        if ($type) {
            $queryBuilder->andWhere('p.type = :type')
                        ->setParameter('type', $type);
        }

        $queryBuilder->orderBy('p.creation_date', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder, // query o queryBuilder
            $request->query->getInt('page', 1), // número de página actual
            5 // límite por página
        );

        return $this->render('main/index.html.twig', [
            'filterForm' => $filterForm->createView(),
            'filtersApplied' => $filtersApplied,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/main/clear-filters', name: 'app_main_clear_filters')]
    public function clearFilters(SessionInterface $session): RedirectResponse
    {
        // Limpiar los filtros de la sesión
        $session->remove('startDate');
        $session->remove('endDate');
        $session->remove('type');

        // Redirigir a la página principal sin filtros
        return $this->redirectToRoute('app_main');
    }


    
    

    #[Route('/create_post', name: 'create_post')]
    public function createPost(Request $request, FileUploader $fileUploader){
        $post = new Post();
        $user = $this->getUser();

        $form = $this-> createForm(PostType::class,$post);
        $form -> handleRequest($request);
        $image = $form->get('File')->getData();

        if ($form->isSubmitted() && $form ->isValid()) {
            $post->setUser($user);
            if ($image) {
                $imageName = $fileUploader->upload($image);
                $post->setFile($imageName);
            }
            $post->setCreationDate($this->date);
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
    public function editPost(Request $request, $id, FileUploader $fileUploader)
    {
        $user = $this->getUser();
        $post = $this->em->getRepository(Post::class)->find($id);

        if ($post->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have permission to edit this post.');
        }

        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        $image = $form->get('File')->getData();
        $oldImage = $post->getFile();

        if ($form->isSubmitted() && $form ->isValid()) {

            if ($image) {
                $imageName = $fileUploader->upload($image);
                $post->setFile($imageName);

                if ($oldImage) {
                    $fileUploader->remove($oldImage);
                }
            }

            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('message','Updated Successfully');
            return $this->redirectToRoute('user_posts');
        }

        return $this->render('main/post.html.twig',[
            'form' => $form->createView()        
        ]);
    }

    #[Route('/detailPost/{id}', name: 'detail_post')]
    public function detailPost(Request $request, Post $post): Response
    {
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setUser($this->getUser());
        $comment->setCreationDate($this->date);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', 'Comment added successfully.');

            return $this->redirectToRoute('detail_post', ['id' => $post->getId()]);
        }

        $comments = $post->getComments();

        return $this->render('main/detailPost.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'commentForm' => $form->createView(),
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

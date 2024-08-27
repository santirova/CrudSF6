<?php
namespace App\Controller;

use App\Entity\Like;
use App\Entity\Post;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    // /**
    //  * @Route("/post/{id}/like", name="post_like", methods={"POST"})
    //  */
    #[Route('/post/{id}/like', name: 'post_like', methods:["POST"])]
    public function like(Post $post, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $date = new DateTimeImmutable();
        $date->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $like = $em->getRepository(Like::class)->findOneBy(['post' => $post, 'user' => $user]);

        if ($like) {
            $em->remove($like);
            $this->addFlash('success', 'You unliked the post.');
            $status = 'unliked';
        } else {
            $like = new Like();
            $like->setPost($post);
            $like->setUser($user);
            $like->setLikedAt($date);

            $em->persist($like);
            $this->addFlash('success', 'You liked the post.');
            $status = 'liked';
        }

        $em->flush();

        // Return the updated count and status
        $likeCount = $post->getLikes()->count();
        return new JsonResponse([
            'status' => $status,
            'likeCount' => $likeCount
        ]);
    }
}

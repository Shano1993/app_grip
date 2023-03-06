<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/add-comment.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    #[Route('/comment/add/{id}', name: 'add_comment')]
    public function addComment(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $date = new DateTime();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setDateAdd($date);
            $comment->setArticle($article);
            $comment->setUser($this->getUser());
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('comment/add-comment.html.twig', [
            'form_comment' => $form->createView(),
        ]);
    }

    #[Route('/comment/edit/{id}', name: 'edit_comment')]
    #[IsGranted('ROLE_MODERATOR')]
    public function editComment(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_home');
        }
        return $this->render('comment/add-comment.html.twig', [
            'form_comment' => $form->createView(),
        ]);
    }

    #[Route('/comment/delete/{id}', name: 'delete_comment')]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(Comment $comment, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
}

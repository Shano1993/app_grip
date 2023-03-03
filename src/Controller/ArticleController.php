<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/', name: 'list_article')]
    public function listArticle(ArticleRepository $articleRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/add', name: 'add_article')]
    public function add(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $container): Response
    {
        $this->denyAccessUnlessGranted("ROLE_AUTHOR");
        $user = $this->getUser();
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($user);
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', "L'article est ajouté avec succès !");
            return $this->redirectToRoute('add_article');
        }

        return $this->render('article/add-article.html.twig', [
            'form_article' => $form->createView(),
        ]);
    }

    #[Route('/article/edit/{id}', name: 'edit_article')]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager) :Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', "L'article a bien été mis à jour !");
            return $this->redirectToRoute('app_home');
        }
        return $this->render('article/add-article.html.twig', [
            'form_article' => $form->createView(),
        ]);
    }

    #[Route('/article/delete/{id}', name: 'delete_article')]
    public function delete(Article $article, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->render('home/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }
}

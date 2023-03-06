<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    #[Route('/', name: 'list_article')]
    public function listArticle(ArticleRepository $articleRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/article/add', name: 'add_article')]
    #[IsGranted('ROLE_AUTHOR')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $form->get('fileName')->getData();
            if ($fileName) {
                $originalFileName = pathinfo($fileName->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $fileName->guessExtension();

                try {
                    $fileName->move(
                        $this->getParameter('fileName_directory'),
                        $newFileName
                    );
                }
                catch (FileException $e) {}
                $article->setFileName($newFileName);
            }
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
    #[IsGranted('ROLE_AUTHOR')]
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
    #[IsGranted('ROLE_AUTHOR')]
    public function delete(Article $article, EntityManagerInterface $entityManager, ArticleRepository $articleRepository): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->render('home/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }
}

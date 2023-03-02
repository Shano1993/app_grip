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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_AUTHOR')]
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
        $user = $this->getUser();
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($user);
            $file = $form['cover']->getData();
            $ext = $file->guessExtension();
            if (!$ext) {
                $ext = 'bin';
            }
            $file->move($container->get("upload.directory"), uniqid() . "." . $ext);
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', "L'article est ajouté avec succès !");
            return $this->redirectToRoute('add_article');
        }

        return $this->render('article/add-article.html.twig', [
            'form_article' => $form->createView(),
        ]);
    }
}

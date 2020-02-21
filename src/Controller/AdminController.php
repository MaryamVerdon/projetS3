<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
use App\Entity\Taille;
use App\Entity\QuantiteTaille;
use App\Form\ArticleType;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/article", name="admin_article")
     */
    public function indexArticle(Request $request)
    {
        $parameters = $request->query->all();
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findByParameters($parameters);
        return $this->render('admin/article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/admin/article/new", name="admin_article_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $article = new Article();
        $article->setLibelle("Nouvel article");
        $article->setPrixU(0.00);
        $article->setImage("/img/example.png");
        $quantiteTaille = new QuantiteTaille();
        $quantiteTaille->setQte(0);
        $article->addQuantiteTaille($quantiteTaille);

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            foreach($article->getQuantiteTailles() as $quantite_taille){
                $quantite_taille->setArticle($article);
            }
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        
        return $this->render('admin/new.html.twig', [
            'controller_name' => 'ArticleController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/article/{id}/edit", name="article_edit")
     */
    public function edit($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        $form = $this->createForm(ArticleType::class);

        
    }

    /**
     * @Route("/admin/article/{id}/remove", name="article_remove")
     */
    public function remove($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()
            ->getManager();
        $article = $em->getRepository(Article::class)
            ->find($id);
        $em->remove($article);
        
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }
}

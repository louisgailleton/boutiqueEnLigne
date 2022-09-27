<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categorie')]
class CategorieController extends AbstractController
{
    /* Affichage des articles d'une catégorie */
    #[Route('/{id}/articles', name: 'app_categorie_articles', methods: ['GET'])]
    public function articles(Categorie $categorie): Response
    {
        return $this->render('categorie/articles.html.twig', [
            'categorie' => $categorie,
            'articles' => $categorie->getArticles()
        ]);
    }
}

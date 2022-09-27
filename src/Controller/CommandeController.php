<?php

namespace App\Controller;

use App\Entity\LigneCommande;
use App\Entity\Commande;
use App\Repository\ArticleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    private $security;
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
       $this->security = $security;
       $this->em = $em;
    }


    /* Ajout d'un article au panier (depuis la page listant les articles d'une catégorie) */
    #[Route('/ajoutPanier', name: 'app_commande_ajoutPanier', methods: ['GET', 'POST'])]
    public function ajoutPanier(ArticleRepository $articleRepository, Request $request)
    {
        $session = new Session();
        
        // Récupération des données envoyées par la requête AJAX
        $donnees = $request->request->all();

        // On vérifie si les idArticle et qte n'ont pas été modifiés
        if(isset($donnees['idArticle']) && $donnees['qte']) {

            $idArticle = $this->protegerVariable($donnees['idArticle']);
            $qteForm = $this->protegerVariable($donnees['qte']);
            
            if(!empty($idArticle) && is_numeric($idArticle) && $idArticle > 0) {
    
                $article = $articleRepository->find($idArticle);
                $nomArticle = $article->getNom();
                $qteArticle = $article->getQte();
                
                // Si la quantité rentré est un chiffre entre 0 et la quantité max de l'article
                if(($qteForm > 0 && $qteForm <= $qteArticle) && is_numeric($qteForm)) {
                    
                    // On vérifie si la session de panier existe
                    $panier = $session->get('panier');
                    if(!isset($panier)) {
                        $panier = [];
                    }
    
                    // S'il n'y a pas d'article dans le panier, on crée une nouvelle ligne
                    if(sizeof($panier) == 0)
                    {
                        $panier = [$nomArticle => ['id' => $idArticle, 'qtePanier' => $qteForm, 'prixU' => $article->getPrix(), 'qteMax' => $qteArticle]];
                        $session->set('panier', $panier);
                    }
                    else { // Sinon on vérifie si une ligne est déjà existante pour l'article, dans ce cas on actualise juste la quantité
                        if(array_key_exists($nomArticle, $panier)) {
                            $panier = $session->get('panier');
                            $panier[$nomArticle]['qtePanier'] = $qteForm;
                            $session->set('panier', $panier);
                        } else {
                            $panier[$nomArticle] = ['id' => $idArticle, 'qtePanier' => $qteForm, 'prixU' => $article->getPrix(), 'qteMax' => $qteArticle];
                            $session->set('panier', $panier);
                        }
                    }
                    
                    return new JsonResponse(['etat' => 'alert-success', 'msg' => 'Votre panier a bien été mis à jour', 'panier' => $panier]);
                }
                else {
                    return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'La quantité ne peut être inférieure à 0 et supérieure à la quantité disponible', 'panier' => null]);
                }
            }
            else {
                return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'Un champ a mal été renseigné, merci de bien vouloir réessayer', 'panier' => null]);
            }
        } else {
            return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'Un champ a mal été renseigné, merci de bien vouloir réessayer', 'panier' => null]);
        }
    }
    
    /* Modification d'une quantité du panier */
    #[Route('/modifQtePanier', name: 'app_commande_modifQtePanier', methods: ['GET', 'POST'])]
    public function modifqtePanier(ArticleRepository $articleRepository, Request $request)
    {
        $session = new Session();

        // Récupération des données envoyées par la requête AJAX
        $donnees = $request->request->all();

        // On vérifie si les idArticle et qte n'ont pas été modifiés
        if(isset($donnees['idArticle']) && $donnees['qte']) {
            
            $idArticle = $this->protegerVariable($donnees['idArticle']);
            $qteForm = $this->protegerVariable($donnees['qte']);

            if(!empty($idArticle) && is_numeric($idArticle) && $idArticle > 0) {

                $article = $articleRepository->find($idArticle);
                $qteArticle = $article->getQte();
                $nomArticle = $article->getNom();
    
                $panier = $session->get('panier');
    
                // Si la quantité rentré est un chiffre entre 0 et la quantité max de l'article
                if(($qteForm > 0 && $qteForm <= $qteArticle) && is_numeric($qteForm)) {                
                    $panier[$nomArticle]['qtePanier'] = $qteForm;
                    $session->set('panier', $panier);

                    return new JsonResponse(['etat' => 'alert-success', 'msg' => 'Votre panier a bien été mis à jour']);
                }
                else if ($qteForm == 0){ // Si la qte choisie est à 0, on supprime la ligne du panier
                    unset($panier[$nomArticle]);
                    $session->set('panier', $panier);
                    
                    return new JsonResponse(['etat' => 'alert-success', 'msg' => 'Votre panier a bien été mis à jour']);
                }
                else {
                    return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'La quantité ne peut être inférieure à 0 et supérieure à la quantité disponible']);
                }
            }
            else {
                return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'Un champ a mal été renseigné, merci de bien vouloir réessayer']);
            }
        } else {
            return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'Un champ a mal été renseigné, merci de bien vouloir réessayer']);
        }
        
        
    }

    /* Affichage du panier */
    #[Route('/panier', name: 'app_commande_panier', methods: ['GET'])]
    public function panier(ArticleRepository $articleRepository): Response
    {
        $session = new Session();
        return $this->render('commande/panier.html.twig', ['panier' => $session->get('panier'), 'articlesBdd' => $articleRepository->findAll()]);
    }

    /* Suppression d'une ligne du panier */
    #[Route('/supprLignePanier', name: 'app_commande_supprLignePanier', methods: ['POST'])]
    public function supprLignePanier(Request $request, ArticleRepository $articleRepository): Response
    {        
        $session = new Session();
        $donnees = $request->request->all();
        if(isset($donnees['idArticle'])) {
            
            $idArticle = $this->protegerVariable($donnees['idArticle']);

            if(!empty($idArticle) && is_numeric($idArticle) && $idArticle > 0) {

                $article = $articleRepository->find($idArticle);
                $nomArticle = $article->getNom();

                $panier = $session->get('panier');
                unset($panier[$nomArticle]);
                if(sizeof($panier) == 0)
                {
                    $session->remove('panier');
                    $panier = null;
                } else {
                    $session->set('panier', $panier);
                }
                
                return new JsonResponse(['etat' => 'alert-success', 'msg' => 'Votre panier a bien été mis à jour', 'panier' => $panier]);

            } else {
                return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'Un champ a mal été renseigné, merci de bien vouloir réessayer', 'panier' => null]);
            }
        } else {
            return new JsonResponse(['etat' => 'alert-danger', 'msg' => 'Un champ a mal été renseigné, merci de bien vouloir réessayer', 'panier' => null]);
        }
    }

    /* Commande */
    #[Route('/commander', name: 'app_commande_commander', methods: ['GET'])]
    public function commander(ArticleRepository $articleRepository): Response
    {
        $session = new Session();
        if ($this->isGranted('ROLE_USER') == true) {
            $user = $this->security->getUser();
            $panier = $session->get('panier');
            $commande = new Commande();
            $montantCommande = 0;

            foreach($panier as $nomArticle => $proprieteArticle) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setCommande($commande);
                $ligneCommande->setIdArticle($proprieteArticle['id']);
                $ligneCommande->setNomArticle($nomArticle);
                $ligneCommande->setQte($proprieteArticle['qtePanier']);
                $ligneCommande->setPrixArticle($proprieteArticle['prixU']);
                $this->em->persist($ligneCommande);

                $article = $articleRepository->find($proprieteArticle['id']);
                $article->setQte($article->getQte() - $proprieteArticle['qtePanier']);
                $this->em->persist($article);                

                $commande->addLigneCommande($ligneCommande);
                $montantCommande += ($proprieteArticle['qtePanier'] * $proprieteArticle['prixU']);
            }
            $commande->setEtatCommande('Validé');
            $commande->setDateCommande(new DateTime());
            $commande->setMontantCommande($montantCommande);
            $commande->setUser($user);
            $this->em->persist($commande);
            $this->em->flush();
            $session->remove('panier');

            return $this->renderForm('commande/commandes.html.twig', ['commandes' => $user->getCommandes()]);
        } else {
            $session->set('envoiVersPanier', 'envoiVersPanier');
            return $this->redirectToRoute('app_login');
        }
    }
    
    /* Affichage des commandes */
    #[Route('/commandes', name: 'app_commande_commandes', methods: ['GET'])]
    public function commandes(): Response
    {
        $user = $this->security->getUser();
        return $this->renderForm('commande/commandes.html.twig', ['commandes' => $user->getCommandes()]);
    }

    /* Affichage d'une commande */
    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'lignesCommande' => $commande->getLigneCommandes()
        ]);
    }

    /* Fonction pour protéger les variables */
    function protegerVariable($donnees){
        $donnees = trim($donnees);
        $donnees = stripslashes($donnees);
        $donnees = htmlspecialchars($donnees);
        return $donnees;
    }
}

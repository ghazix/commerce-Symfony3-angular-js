<?php

namespace Client\ClientBundle\Controller;

use Client\ClientBundle\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PanierController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClientBundle:Panier:index.html.twig');
    }

    public function addPanierAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $qty = $request->request->get('qty');
        $client = $this->get('security.token_storage')->getToken()->getUser();
        $produit = $em->getRepository('ProjetBundle:Produit')->find($id);
        $panier = new Panier();
        $panier->setClient($client);
        $panier->setProduit($produit);
        $panier->setQtyPanier($qty);
        $panier->setDatePanier(new \DateTime('now'));
        $em->persist($panier);
        $em->flush();

        return new JsonResponse('ajout ok');
    }

    public function getPanierAction()
    {
        $em = $this->getDoctrine()->getManager();
        $client = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $paniersArray = array();
        $query = $em->createQuery("
                                  select 
                                  sum(p.qtyPanier) as qty, 
                                  p.id as idPanier, 
                                  pr.id as idProduit,
                                  pr.nomProduit as nomProduit,
                                  pr.prixProduit as unitPrice,
                                  d.id as dispoId,
                                  d.titreDisponibilite as dispoTitre,
                                  d.classDisponibilite as dispoClass,
                                  (pr.prixProduit * sum(p.qtyPanier)) as prixProduit 
                                  from ClientBundle:Panier p, ProjetBundle:Produit pr, ProjetBundle:Disponibilite d WHERE (pr.disponibilite = d.id and p.produit = pr.id and p.client = $client) group by p.produit");
        $result = $query->getResult();

        $s = 0;
        $i = 0;
        foreach ($result as $panier){
            $s = $s + $panier['prixProduit'];
            $i++;
            array_push($paniersArray, array(
                'idPanier' => $panier['idPanier'],
                'idProduit' => $panier['idProduit'],
                'nomProduit' => $panier['nomProduit'],
                'prixProduit' => $panier['prixProduit'],
                'unitPrice' => $panier['unitPrice'],
                'dispo' => $panier['dispoTitre'],
                'dispoClass' => $panier['dispoClass'],
                'qtePanier' => $panier['qty']
            ));
        }


        return new JsonResponse(array(
            'panier' => $paniersArray,
            'prixTotal' => $s,
            'nbrPanier' => $i
        ));
    }

    public function deletePanierAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $paniers = $em->getRepository('ClientBundle:Panier')->findBy(array('produit' => $id));
        foreach ($paniers as $panier ){
            $em->remove($panier);
            $em->flush();
        }



        return new JsonResponse('del ok');
    }
}

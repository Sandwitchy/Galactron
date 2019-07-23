<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Entity\Univers;
use App\Entity\User;
use App\Form\UserType;

class UniversController extends AbstractController
{
    /**
     * @Route("/new_universe", name="create_universe")
     */
    public function create(Request $request ,Security $security )
    {
        $universe = new Univers();
        $user = $security->getUser();
        dump($user);
        if ($request->request->get('name') !== null) {
             dump($request);
            $universe -> setName($request->request->get('name'));
            $universe -> setCreator($user);
            if($request->request->get('image') !== ""){
                $universe -> setImage($request->request->get('image'));
            }else{
                $universe -> setImage("default.png");
            }

        //  4) save the Univers!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($universe);
            $entityManager->flush();

             // INSERER ALERT SUCCESS 

             return $this->redirectToRoute('dashboard');
        }
        return $this->render('univers/index.html.twig', [
            'controller_name' => 'UniversController',
        ]);
    }
}

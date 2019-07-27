<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Entity\Univers;
use App\Entity\UserUnivers;
use App\Entity\User;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function index(Security $security)
    {   
        $user = $security->getUser();
        if($user !== null){
            $userUnivers = $user -> getUserUnivers();
        
            $universInFav = array();
            foreach($userUnivers as $univers){
                array_push($universInFav,$univers->getUnivers());
            }
            
        }else{
            $universInFav = "";
        }
        $universes = $this  ->getDoctrine()
                            ->getRepository(Univers::class)
                            ->findBy(
                                ['isPrivate' => false]
                            );
        return $this->render('dashboard/index.html.twig', [
            'universes' => $universes,
            'universesInFav' => $universInFav,
        ]);
    }

    /**
     * @Route("/addFavorite/{id}", name="addFavorite" , methods={"GET"})
     */
    public function addFavorite(Univers $univers,Security $security)
    {   
        $user = $security->getUser();

        $userUnivers = $user -> getUserUnivers();
        
        $userUnivers = new UserUnivers();
        $userUnivers-> setUser($user)
                    -> setUnivers($univers)
                    -> setNameRole('visitor');

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($userUnivers);
        $entityManager->flush();

        // INSERER ALERT SUCCESS 
        $this->addFlash(
            'success',
            "L'univers à été enregistré dans vos favoris !"
        );

        return $this->redirectToRoute('dashboard');
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Entity\Univers;
use App\Entity\UserUnivers;
use App\Entity\User;
use App\Form\UserType;
use App\Service\FileUploader;

class UniversController extends AbstractController
{
    /**
     * @Route("/new_universe", name="create_universe")
     */
    public function create(Request $request ,Security $security ,FileUploader $fileUploader)
    {
        $universe = new Univers();
        $user = $security->getUser();
        dump($user);
        if ($request->request->get('name') !== null) {

            $universe -> setName($request->request->get('name'));
            $universe -> setCreator($user);
            if($request->files->get('image') !== null){

                $file = $request->files->get('image');
                $nameFile = $fileUploader->upload($file);

                $universe -> setImage($nameFile);
            }else{
                $universe -> setImage("default.png");
            }
            
        //  4) save the Universe!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($universe);

            $userUnivers = new UserUnivers();
            $userUnivers->setUser($user)
                        ->setNameRole('creator')
                        ->setUnivers($universe);

            $entityManager->persist($userUnivers);
            
            $entityManager->flush();

             // INSERER ALERT SUCCESS 

             return $this->redirectToRoute('dashboard');
        }
        return $this->render('univers/index.html.twig', [
            'controller_name' => 'UniversController',
        ]);
    }
    /**
     * @Route("univers/{id}", name="univers_show", methods={"GET"})
    */
    public function show(Univers $universe,Security $security)
    {
        $user = $security->getUser();

        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;

        return $this->render('univers/show.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
        ]);
    }

    /**
     * @Route("univers/parameters/{id}", name="univers_parameters", methods={"GET","POST"})
    */
    public function edit(Univers $universe,Security $security,Request $request,FileUploader $fileUploader)
    {
        $user = $security->getUser();

        if ($request->request->get('name') !== null) {
            $universe -> setName($request->request->get('name'));
            $universe -> setCreator($user);
            dump($request);
            if($request->files->get('image') !== null){

                $file = $request->files->get('image');
                $nameFile = $fileUploader->upload($file);

                $universe -> setImage($nameFile);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($universe);
            $entityManager->flush();

            // INSERER ALERT SUCCESS 
            $this->addFlash(
                'success',
                'Vos changements ont bien été enregistré !'
            );

            return $this->redirectToRoute('univers_parameters', [
                'id' => $universe->getId(),
            ]);
        }

        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;

        return $this->render('univers/parameters.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
        ]);
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Entity\Univers;
use App\Entity\UserUnivers;
use App\Entity\User;
use App\Entity\ContentType;
use App\Entity\Content;
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
     * 
    */
    public function edit(Univers $universe,Security $security,Request $request,FileUploader $fileUploader)
    {
        $user = $security->getUser();

        // update des infos de base de l'univers
        if ($request->request->get('name') !== null) {
            $universe -> setName($request->request->get('name'));
            $universe -> setCreator($user);
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
        // ajout d'un ContentType
        if($request->request->get('contentTypeName') !== null){
            $contentType = new ContentType();
            $contentType->setName($request->request->get('contentTypeName'))
                        ->setUnivers($universe);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contentType);
            $entityManager->flush();

             // INSERER ALERT SUCCESS 
             $this->addFlash(
                'success',
                'La catégorie a bien été créé !'
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

    /**
     * @Route("univers/gestion/{id}", name="univers_gestion", methods={"GET","POST"})
    */
    public function gestion(Univers $universe,Security $security)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();

        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;

        return $this->render('univers/gestion.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
        ]);
    }
    /**
     * @Route("univers/gestion/new/{id}", name="univers_new_content", methods={"GET","POST"})
    */
    public function newContent(Univers $universe,Security $security,Request $request,FileUploader $fileUploader)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();

        if($request->request->get('name') !== null){
            $content = new Content();

            $content -> setName($request->request->get('name'))
                     -> setContent($request->request->get('content'))
                     -> setAuthor($user)
                     -> setUnivers($universe);

            ($request->request->get('isPrivate') == true)? $content->setIsPrivate(true) : $content->setIsPrivate(false);
            if($request->request->get('description') !== null){
                $content->setDescription($request->request->get('description'));
            }
            if($request->request->get('contentType') !== null){
                $id = $request->request->get('contentType');
                $contentType = $this->getDoctrine()
                            ->getRepository(ContentType::class)
                            ->find($id);
                $content->setContentType($contentType);
            }
            if($request->files->get('image') !== null){

                $file = $request->files->get('image');
                $nameFile = $fileUploader->upload($file);

                $content -> setImage($nameFile);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($content);
            $entityManager->flush();

             // INSERER ALERT SUCCESS 
             $this->addFlash(
                'success',
                'Votre contenu à bien été enregistré !'
            );

            return $this->redirectToRoute('univers_gestion', [
                'id' => $universe->getId(),
            ]);
        }

        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;

        return $this->render('content/new.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
        ]);
    }
}

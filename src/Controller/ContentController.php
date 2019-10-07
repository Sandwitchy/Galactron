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
use App\Entity\Message;
use App\Form\UserType;
use App\Service\FileUploader;
use App\Service\MessageSystemService;

class ContentController extends AbstractController
{
    /**
     * @Route("univers/{id}/gestion", name="univers_gestion", methods={"GET","POST"})
    */
    public function gestion(Univers $universe,Security $security)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();

        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
        $isRedactor = $this->checkIfRedactor($user,$universe);
        if(($isCreator == false)&&($isRedactor == false)){
            $this->addFlash(
                'danger',
                "Vous n'avez pas les droits d'accès à cette page."
            );
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('univers/gestion.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor
        ]);
    }
    /**
     * @Route("univers/{id}/gestion/new/", name="univers_new_content", methods={"GET","POST"})
    */
    public function newContent(Univers $universe,Security $security,Request $request,FileUploader $fileUploader)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();

        $isRedactor = $this->checkIfRedactor($user,$universe);
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
        if(($isRedactor == false)&&($isCreator == false)){
           // INSERER ALERT SUCCESS 
           $this->addFlash(
            'danger',
            'Vous faites quoi?'
            );

            return $this->redirectToRoute('dashboard'); 
        }

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
                $ids = $request->request->get('contentType');
                foreach($ids as $id){
                    $contentType = $this->getDoctrine()
                        ->getRepository(ContentType::class)
                        ->find($id);
                    $content->addContentType($contentType);
                }
               
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

        return $this->render('content/new.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor
        ]);
    }
    /**
     * @Route("univers/{id}/gestion/edit/{idContent}", name="univers_edit_content", methods={"GET","POST"})
    */
    public function editContent(Univers $universe,Security $security,Request $request,FileUploader $fileUploader,$idContent)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();
        $content =  $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->find($idContent);
        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
        $isRedactor = $this->checkIfRedactor($user,$universe);

        if(($isRedactor == false)&&($isCreator == false)){
           // INSERER ALERT SUCCESS 
           $this->addFlash(
            'danger',
            'Vous faites quoi?'
            );

            return $this->redirectToRoute('dashboard'); 
        }
        if($request->request->get('name') !== null){
           

            $content -> setName($request->request->get('name'))
                     -> setContent($request->request->get('content'))
                     -> setAuthor($user)
                     -> setUnivers($universe);

            ($request->request->get('isPrivate') == true)? $content->setIsPrivate(true) : $content->setIsPrivate(false);
            if($request->request->get('description') !== null){
                $content->setDescription($request->request->get('description'));
            }
            if($request->request->get('contentType') !== null){
                $ids = $request->request->get('contentTypes');
                foreach($ids as $id){
                    $contentType = $this->getDoctrine()
                        ->getRepository(ContentType::class)
                        ->find($id);
                    $content->addContentType($contentType);
                }
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

        return $this->render('content/edit.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor,
            'content' => $content
        ]);
    }
    /**
     * @Route("univers/{id}/gestion/show/{idContent}", name="univers_show_content", methods={"GET","POST"})
    */
    public function showContent(Univers $universe,Security $security,$idContent,Request $request)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();
        $content =  $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->find($idContent);
        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
        $isRedactor = $this->checkIfRedactor($user,$universe);
        $showAsVisitor = false;
        if($request->isMethod('post')){
            if($request->request->get('state') == null){
                $showAsVisitor = true;
            }elseif($request->request->get('state') == true){
                $showAsVisitor = false;
            }else{
                $showAsVisitor = true;
            }
        }
           
        return $this->render('content/show.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor,
            'content' => $content,
            'showAsVisitor' => $showAsVisitor,
        ]);
    }
    /**
     * @Route("univers/{id}/gestion/delete/{idContent}", name="univers_delete_content", methods={"GET"})
    */
    public function deleteContent(Univers $universe,Security $security,$idContent)
    {
        // récupère les infos de l'user connecté
        $user = $security->getUser();
        $content =  $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->find($idContent);

        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
        if($isCreator == false){
           // INSERER ALERT danger 
            $this->addFlash(
                'danger',
                'Vous venez souvent par ici?'
            );
            return $this->redirectToRoute('dashboard'); 
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($content);
        $entityManager->flush();

         // INSERER ALERT danger 
         $this->addFlash(
            'danger',
            'Votre contenu à bien été supprimé...'
        );
        return $this->redirectToRoute('univers_gestion', [
            'id' => $universe->getId(),
        ]);
    }
    public function checkIfRedactor(User $user, Univers $universe){
        $userUnivers = $user->getUserUnivers();

        foreach($userUnivers as $uU){
            if($uU->getUnivers() == $universe){
                if($uU->getNameRole() === 'redactor'){
                    return true;
                }
            }
        }
        return false;
    }
}
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

class UniversController extends AbstractController
{
    /**
     * @Route("/new_universe", name="create_universe")
     */
    public function create(Request $request ,Security $security ,FileUploader $fileUploader)
    {
        $universe = new Univers();
        $user = $security->getUser();

        if ($request->request->get('name') !== null) {

            $universe   -> setName($request->request->get('name')) 
                        -> setCreator($user)
                        -> setIsPrivate(true);
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
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor
        ]);
    }
    /**
     * @Route("univers/{id}", name="univers_show", methods={"GET"})
    */
    public function show(Univers $universe,Security $security)
    {
        // met a jour le nombre de content public
        $contentTypes = $this->getDoctrine()
                            ->getRepository(ContentType::class)
                            ->findAll();
        $entityManager = $this->getDoctrine()->getManager();
        foreach($contentTypes as $contentType){
            $contentType->setNbrContents();
            $entityManager->persist($contentType);
        }
        $entityManager->flush();
        

        $user = $security->getUser();
        
        $contents = $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->findBy(
                        ['isPrivate' => false,
                        'univers' => $universe->getId()]
                    );
        
        // check si l'user connecté est l'admin de l'univers
        if($user !== null){
            ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
            $isRedactor = $this->checkIfRedactor($user,$universe);
        }else{
            $isRedactor = false;
            $isCreator = false;
        }
        return $this->render('univers/show.html.twig', [
            'universe' => $universe,
            'contents' => $contents,
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor,
        ]);
    }

     /**
     * @Route("univers/{id}/categorie/{idCat}", name="univers_category", methods={"GET"})
    */
    public function categorie(Univers $universe,Security $security,$idCat)
    {
        // met a jour le nombre de content public
        $contentType = $this->getDoctrine()
                            ->getRepository(ContentType::class)
                            ->find($idCat);
        $contents = $this   ->getDoctrine()
                            ->getRepository(Content::class)
                            ->findBy(
                                ['isPrivate' => false,
                                'contentType' => $contentType->getId()]
                            );
        $user = $security->getUser();
        
        // check si l'user connecté est l'admin de l'univers
        ($universe->getCreator() == $user)? $isCreator = true :  $isCreator = false;
        $isRedactor = $this->checkIfRedactor($user,$universe);

        return $this->render('univers/category.html.twig', [
            'universe' => $universe,
            'contentType' => $contentType,
            'contents' => $contents,
            'isCreator' => $isCreator,
            'isRedactor' => $isRedactor
        ]);
    }
    /**
     * @Route("univers/{id}/parameters", name="univers_parameters", methods={"GET","POST"})
     * 
    */
    public function edit(Univers $universe,Security $security,Request $request,FileUploader $fileUploader,MessageSystemService $messSystem)
    {
        $user = $security->getUser();
        $entityManager = $this->getDoctrine()->getManager();

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

        // update des infos de base de l'univers
        if ($request->request->get('name') !== null) {
            $universe -> setName($request->request->get('name'));
            $universe -> setCreator($user);

            ($request->request->get('isPrivate') == true)? $universe -> setIsPrivate(true) : $universe -> setIsPrivate(false);
            if($request->files->get('image') !== null){

                $file = $request->files->get('image');
                $nameFile = $fileUploader->upload($file);

                $universe -> setImage($nameFile);
            }

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
        // envoie invit redactor
        if ($request->request->get('userId') != null) {
            if($request->request->get('userName') != null){
                $userId = $request->request->get('userId');
                $newRedacteur = $this->getDoctrine()
                                    ->getRepository(User::class)
                                    ->find($userId);
                if($newRedacteur->getUsername() == ($request->request->get('userName'))){

                    $messSystem -> sendPromoteRedactor($newRedacteur,$user,$universe);

                     // Success
                    $this->addFlash(
                        'success',
                        "L'utilisateur ".$newRedacteur->getUsername()." va recevoir une invitation pour devenir rédacteur de ". $universe->getName()
                    );
                    return $this->redirectToRoute('univers_parameters', [
                        'id' => $universe->getId(),
                    ]);
                }
                
            }
            // ERREUR
            $this->addFlash(
                'danger',
                'Une erreur est survenue, veuillez réessayez'
            );
            return $this->redirectToRoute('univers_parameters', [
                'id' => $universe->getId(),
            ]);
        }
        
        // ajout d'un ContentType
        if($request->request->get('contentTypeName') !== null){
            $contentType = new ContentType();
            $contentType->setName($request->request->get('contentTypeName'))
                        ->setUnivers($universe)
                        ->setNbrContents();

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
        
        //récupération de la liste des rédacteurs
        $users = $universe->getUserUnivers();
        $redactors = array();
        foreach($users as $use){
            if($use->getNameRole() === 'redactor'){
                $redactors[] = $use->getUser();
            }
        }
        //update ContentType
        if($request->request->get('nameContentType') !== null){
            $name = $request->request->get('nameContentType');
            $id = $request->request->get('idContentType');
            $contentType = $this->getDoctrine()
                            ->getRepository(ContentType::class)
                            ->find($id);
            $contentType -> setName($name);

            $entityManager->persist($contentType);
            $entityManager->flush();
             // INSERER ALERT SUCCESS 
             $this->addFlash(
                'success',
                'La catégorie a bien été mise à jour !'
            );

            return $this->redirectToRoute('univers_parameters', [
                'id' => $universe->getId(),
            ]);

        }
        return $this->render('univers/parameters.html.twig', [
            'universe' => $universe,
            'isCreator' => $isCreator,
            'redactors' => $redactors,
        ]);
    }
     /**
     * @Route("univers/deletecategory/{id}", name="category_delete", methods={"GET"})
     * 
    */
    public function deleteCategory(ContentType $contentType,Security $security)
    {
        $user = $security->getUser();
        $universe = $contentType -> getUnivers();
        if($universe->getCreator() == $user){
            $contents = $contentType->getContents();

            $entityManager = $this->getDoctrine()->getManager();
            foreach($contents as $content){
                $content->setContentType(null);
                $entityManager -> persist($content);
            }

            $entityManager->remove($contentType);

            $entityManager->flush();
             // INSERER ALERT danger 
             $this->addFlash(
                'danger',
                'La catégories à bien été supprimer. Si du contenu appartenait à cet catégories, ils sont maintenant sans catégories.'
            );
            return $this->redirectToRoute('univers_parameters', [
                'id' => $universe->getId(),
            ]);
        }
         // INSERER ALERT danger 
         $this->addFlash(
            'danger',
            "Tu essaies d'accomplir quelque chose ?"
            );
        return $this->redirectToRoute('dashboard');

    }
    /**
     * @Route("univers/delete/{id}", name="univers_delete", methods={"GET"})
     * 
    */
    public function delete(Univers $universe,Security $security)
    {
        $user = $security->getUser();
        if($universe->getCreator() == $user){

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($universe);

            $entityManager->flush();
             // INSERER ALERT danger 
             $this->addFlash(
                'deleteUnivers',
                'Votre univers à bien été supprimé, nous sommes désolé que vous aillez choisis Galactron...'
            );
            return $this->redirectToRoute('dashboard');
        }
         // INSERER ALERT danger 
         $this->addFlash(
            'danger',
            "Tu essaies d'accomplir quelque chose ?"
            );
        return $this->redirectToRoute('dashboard');

    }
    
}

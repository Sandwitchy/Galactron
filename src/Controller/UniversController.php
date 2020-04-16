<?php

namespace App\Controller;

use App\Form\UniversType;
use App\Service\Security\RoleChecker;
use App\Service\Univers\RedactorManager;
use App\Service\Univers\UniversCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Entity\Univers;
use App\Entity\User;
use App\Entity\ContentType;
use App\Entity\Content;
use App\Service\FileUploader;
use App\Service\MessageSystemService;

class UniversController extends AbstractController
{
    /**
     * @Route("/new_universe", name="create_universe")
     * @param Request $request
     * @param UniversCreator $creator
     * @return RedirectResponse|Response
     */
    public function create(Request $request ,UniversCreator $creator)
    {
        $form = $this->createForm(UniversType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $univers = $form->getData();
            $creator->createUnivers($univers,$this->getUser(),$request->files->get('image'));

            $this->addFlash("success","L'univers à été créer !");
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('univers/index.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("univers/{id}", name="univers_show", methods={"GET"})
     * @param Univers $universe
     * @param Security $security
     * @param RoleChecker $roleChecker
     * @return Response
     */
    public function show(Univers $universe,Security $security,RoleChecker $roleChecker)
    {
        // met a jour le nombre de content public
        //TODO: refactor this
        $contentTypes = $this->getDoctrine()
                            ->getRepository(ContentType::class)
                            ->findAll();
        $entityManager = $this->getDoctrine()->getManager();
        foreach($contentTypes as $contentType){
            $contentType->setNbrContents();
            $entityManager->persist($contentType);
        }
        $entityManager->flush();


        $contents = $this->getDoctrine()
                    ->getRepository(Content::class)
                    ->findBy(
                        ['isPrivate' => false,
                        'univers' => $universe->getId()]
                    );
        
        $roles  = $roleChecker->check($this->getUser(),$universe);
        return $this->render('univers/show.html.twig', [
            'universe' => $universe,
            'contents' => $contents,
            'isCreator' => $roles[0],
            'isRedactor' => $roles[1],
        ]);
    }

     /**
     * @Route("univers/{id}/categorie/{idCat}", name="univers_category", methods={"GET"})
    */
    public function categorie(Univers $universe,Security $security,$idCat,RoleChecker $roleChecker)
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

        $roles  = $roleChecker->check($this->getUser(),$universe);

        return $this->render('univers/category.html.twig', [
            'universe' => $universe,
            'contentType' => $contentType,
            'contents' => $contents,
            'isCreator' => $roles[0],
            'isRedactor' => $roles[1]
        ]);
    }

    /**
     * @Route("univers/{id}/parameters", name="univers_parameters", methods={"GET","POST"})
     * @param Univers $universe
     * @param Security $security
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param MessageSystemService $messSystem
     * @param RoleChecker $roleChecker
     * @param RedactorManager $redactorManager
     * @return RedirectResponse|Response
     */
    public function edit(Univers $universe,
                         Security $security,
                         Request $request,
                         FileUploader $fileUploader,
                         MessageSystemService $messSystem,
                         RoleChecker $roleChecker,
                         RedactorManager $redactorManager)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $roles  = $roleChecker->check($this->getUser(),$universe);
        if(!$roles[0] && !$roles[1]){
            $this->addFlash(
                'danger',
                "Vous n'avez pas les droits d'accès à cette page."
            );
            return $this->redirectToRoute('dashboard');
        }
        //---------------------------------------------
        // update des infos de base de l'univers
        //---------------------------------------------
        $formUnivers = $this->createForm(UniversType::class,null,['update' => true]);

        $formUnivers->handleRequest($request);
        if ($formUnivers->isSubmitted() && $formUnivers->isValid()) {
            $universe = $formUnivers->getData();

            //TODO : improve this code ...
            if($request->files->get('image') !== null){
                $nameFile = $fileUploader->upload($request->files->get('image'));
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
        //---------------------------------------------
        // envoie invit redactor
        //---------------------------------------------

        if ($request->request->get('userId') != null) {
            $redactorManager->promote($request->request->get('userId'),$this->getUser(),$universe);
             // Success
            $this->addFlash(
                'success',
                "L'utilisateur va recevoir une invitation pour devenir rédacteur de ". $universe->getName()
            );

            return $this->redirectToRoute('univers_parameters', [
                'id' => $universe->getId(),
            ]);
        }
        //---------------------------------------------
        // ajout d'un ContentType
        //---------------------------------------------
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

        $redactors = $redactorManager->fetchRedactor($universe);
        return $this->render('univers/parameters.html.twig', [
            'universe' => $universe,
            "formUnivers" => $formUnivers->createView(),
            'isCreator' => $roles[0],
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

    //useless
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

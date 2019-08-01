<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Univers;
use App\Entity\UserUnivers;
use App\Entity\Message;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\FileUploader;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    /**
     * @Route("/register", name="register_user", methods={"POST","GET"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            // Profile pics by default
            $user->setImage('default.png');
            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // INSERER ALERT SUCCESS 

            return $this->redirectToRoute('dashboard');
        }
        return $this->render('user/register.html.twig',[
             'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/parameter", name="parameter_user", methods={"POST","GET"})
     */
    public function parameter(Request $request,Security $security, UserPasswordEncoderInterface $passwordEncoder,FileUploader $fileUploader)
    {
        
        // 1) build the form
        $user = $security -> getUser();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);

        if($request->files->get('image') !== null){
            $image = $request->files->get('image');
            if($image !== $user->getImage()){

                $nameFile = $fileUploader->upload($image);
                $user -> setImage($nameFile);
                // 4) save the User!
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // INSERER ALERT SUCCESS 
                 // ERREUR
                $this->addFlash(
                    'success',
                    'Votre image à bien été mise à jour'
                );
                return $this->redirectToRoute('parameter_user');
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // INSERER ALERT SUCCESS 
             // ERREUR
             $this->addFlash(
                'success',
                'Vos imformation ont bien été mise à jour'
            );
            return $this->redirectToRoute('parameter_user');
        }
        return $this->render('user/user.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    
     /**
     * @Route("/Users_Json", name="users_json", methods={"POST","GET"})
     */
    public function userJson(Request $request, Security $security)
    {
            $userLog = $security->getUser();
            $users = $this->getDoctrine()
                            ->getRepository(User::class)
                            ->findAll();
            $json = array();
            foreach($users as $user){
                if($user !== $userLog){
                    array_push($json,[
                        'text' => $user->getUsername() ,
                        'icon' => $user->getImage(),
                        'id' => $user->getId()
                    ]);
                }
            }
            return $this->json($json);        
    }
     /**
     * @Route("/PromoteUser/{id}", name="promoteUser", methods={"GET"})
     */
    public function promoteUser(Request $request, Security $security,Univers $universe)
    {
        $user = $security->getUser();
        
        $userUnivers = $user->getUserUnivers();
        
        foreach($userUnivers as $uU){
            if($uU->getnameRole() === 'waiting_promote'){
                if($uU->getUnivers() == $universe){
                    $uU -> setNameRole('redactor');

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($uU);
                    $entityManager->flush();

                    // INSERER ALERT SUCCESS 
                    $this->addFlash(
                        'success',
                        "Félicitation !! Vous êtes devenu rédacteur de ".$universe->getName(). "!!"
                    );
                    return $this->redirectToRoute('dashboard');
                }
            }
        }
        // Error l'user n'as pas les autorisation d'etre promu
        $this->addFlash(
            'danger',
            "Vous venez souvent par ici?"
        );
        return $this->redirectToRoute('dashboard');
            
    }
    /**
     * @Route("univers/deleteRedactor/{id}/{idUnivers}", name="redactor_delete", methods={"GET"})
     * 
    */
    public function deleteRedactor(User $user,$idUnivers,Security $security)
    {
        $userAdmin = $security->getuser();
        $universe = $this->getDoctrine()
                        ->getRepository(Univers::class)
                        ->find($idUnivers);
        if($universe->getCreator() == $userAdmin){
            
            $uU = $this->getDoctrine()
            ->getRepository(UserUnivers::class)
            ->findOneBy([
                'User' => $user->getId(),
                'Univers' => $universe->getId()
            ]);
            $uU -> setNameRole("visitor");

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($uU);

            //notification envoyé à l'user rétrogradé
            $message = new Message();
            $message->setToUser($user)
                    ->setTitle('Rétrogradé de '.$universe->getName())
                    ->setIsRead(false)
                    ->setIsSystem(true)
                    ->setContent("
                            Nous sommes dans le regret de vous annoncé que vous avez été rétrogradé. Ce qui signifie que vous n'êtes plus rédacteur au sein de ".
                            $universe->getName().". Nous sommes navrés que votre collaboration avec ". $universe->getCreator()." ce termine maintenant.
                    ");

            $entityManager->persist($message);

            $entityManager->flush();

            // INSERER ALERT danger 
            $this->addFlash(
                'danger',
                'Le rédacteur a bien été rétrogradé. Il sera notifié de votre décision'
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
}

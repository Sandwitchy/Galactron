<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function parameter(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);

        if($request){
            dump($request);
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

            return $this->redirectToRoute('dashboard');
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
}

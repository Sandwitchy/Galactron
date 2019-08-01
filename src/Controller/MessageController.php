<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MessageController extends AbstractController
{
    /**
     * @Route("/message", name="message")
     */
    public function index(Security $security)
    {
        $user = $security->getUser();
        $messages = $this ->getDoctrine()
                            ->getRepository(Message::class)
                            ->findBy(['toUser' => $user->getId()],['createdAt' => "ASC"]);
        
        return $this->render('message/index.html.twig', [
            'messages' => $messages,
        ]);
    }

    /**
     * @Route("/message/new", name="message_new")
     */
    public function create(Security $security, Request $request,EntityManagerInterface $entityManager)
    {
        $user = $security->getUser();
        if($request->request->get('title')){
            $toUser = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->find($request->request->get('userId'));

            $message = new Message();
            $message->setToUser($toUser)
                    ->setFromUser($user)
                    ->setTitle($request->request->get('title'))
                    ->setIsRead(false)
                    ->setIsSystem(false)
                    ->setContent($request->request->get('content'));

            $entityManager->persist($message);
            $entityManager->flush();

            // INSERER ALERT SUCCESS 
            $this->addFlash(
                'success',
                "Le message a bien été envoyé"
            );
    
            return $this->redirectToRoute('message'); 
        }
        return $this->render('message/new.html.twig', [
        ]);
    }
    /**
     * @Route("/message/{id}", name="message_show" , methods={"GET"})
     */
    public function show(Security $security, Message $message,EntityManagerInterface $entityManager)
    {
        $user = $security->getUser();
        ($message->getIsRead() == false)? $message->setIsRead(true): null ;
        
        $entityManager->persist($message);
        $entityManager->flush();
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    /**
     * @Route("/message/delete/{id}", name="message_delete" , methods={"GET"})
     */
    public function delete(Security $security, Message $message,EntityManagerInterface $entityManager)
    {
        $user = $security->getUser();
        if($user == $message->getToUser()){
            if($message->getIsSystem() == true){
                $entityManager->remove($message);
            }else{
                $user->removeMessage($message);
                $entityManager->persist($user);
            }
    
            $entityManager->flush();
            // INSERER ALERT SUCCESS 
            $this->addFlash(
                'danger',
                "Le message a bien été supprimé"
            );
    
            return $this->redirectToRoute('message');
        }else{
            // INSERER ALERT SUCCESS 
            $this->addFlash(
                'danger',
                "Bonjour, vous venez souvent par ici?"
            );
    
            return $this->redirectToRoute('dashboard');
        }
        
    }

}

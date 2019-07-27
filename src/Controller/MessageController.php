<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class MessageController extends AbstractController
{
    /**
     * @Route("/message", name="message")
     */
    public function index(Security $security)
    {
        $user = $security->getUser();
        $messages = $user -> getMessages();

        
        return $this->render('message/index.html.twig', [
            'messages' => $messages,
        ]);
    }
}

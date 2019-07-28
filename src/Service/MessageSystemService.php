<?php
    // src/Service/MessageSystemService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Univers;
use App\Entity\Message;
use Symfony\Component\Templating\EngineInterface;

class MessageSystemService
{
    private $templating;
    private $manager;

    public function __construct(EntityManagerInterface $entityManager,\Twig_Environment $templating){
        $this->manager = $entityManager;
        $this->templating = $templating;
    }

    public function sendPromoteRedactor(User $user,User $from,Univers $universe){
        //envoi invitation 
        $invitation = new Message();
        $invitation -> setToUser($user)
                    -> setTitle($from.' vous invite à devenir rédacteur sur '.$universe->getName())
                    -> setIsSystem(true)
                    -> setIsRead(false)
                    -> setContent($this->templating->render(
                                            'message/invitation.html.twig',
                                            [
                                                'fromUser' => $from,
                                                'newUser' => $user,
                                                'univers' => $universe
                                            ]
                                        ),
                                        'text/html'
                                    );
        $entityManager = $this->manager;
        $entityManager->persist($invitation);
        $entityManager->flush();
    }

}
?>
<?php
    // src/Service/MessageSystemService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Univers;
use App\Entity\UserUnivers;
use App\Entity\Message;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Templating\EngineInterface;

class MessageSystemService
{
    private $templating;
    private $manager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager,\Twig_Environment $templating,Security $security){
        $this->manager = $entityManager;
        $this->templating = $templating;
        $this->security = $security;
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
        $userUnivers = $entityManager->getRepository(UserUnivers::class)
                        ->findOneBy([
                            'User' => $user->getId(),
                            'Univers' => $universe->getId()
                        ]);
        if($userUnivers == null){
            $userUnivers -> setUser($user)
            -> setUnivers($universe)
            -> setNameRole('waiting_promote');
        }else{
            $userUnivers -> setNameRole('waiting_promote');
        }
        
        $entityManager->persist($invitation);
        $entityManager->persist($userUnivers);
        $entityManager->flush();
    }
    
    public function countMessage(){
        $user = $this->security->getUser();
        $messages = $user->getMessages();
        $i = 0;
        foreach($messages as $message){
           if($message->getIsRead() == false){
               $i++;
           } 
        }
        return $i;
    }
}
?>
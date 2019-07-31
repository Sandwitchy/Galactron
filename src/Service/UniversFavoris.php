<?php
// src/Service/UniversFavoris.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UniversFavoris
{
    private $universes;
    private $security;

    public function __construct(Security $security)
    {
        $this->universes = array();
        $this->security = $security;
    }

    public function getFavorites()
    {
        
        $user = $this->security->getUser();

        $userUnivers = $user -> getUserUnivers();
        $universes = $this->universes;
        foreach($userUnivers as $univers){
            if($univers -> getNameRole() !== 'waiting_promote' ){
                array_push($universes,$univers->getUnivers());
            }
        }

        $this->universes = $universes;

        return $universes;
    }
}

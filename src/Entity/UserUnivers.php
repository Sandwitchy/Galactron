<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserUniversRepository")
 */
class UserUnivers
{
    use DefaultObject;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameRole;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userUnivers")
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Univers", inversedBy="userUnivers")
     */
    private $Univers;

    public function __construct()
    {
        $this->Users = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return parent::getId();
    }
    public function getNameRole(): ?string
    {
        return $this->nameRole;
    }

    public function setNameRole(string $nameRole): self
    {
        $this->nameRole = $nameRole;

        return $this;
    }

    public function getUser()
    {
        return $this->User;
    }

    public function setUser(User $user): self
    {
        $this->User = $user;

        return $this;
    }

    public function getUnivers(): ?Univers
    {
        return $this->Univers;
    }

    public function setUnivers(?Univers $Univers): self
    {
        $this->Univers = $Univers;

        return $this;
    }
}

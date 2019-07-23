<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UniversRepository")
 */
class Univers extends DefaultObject
{

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="univers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserUnivers", mappedBy="Univers")
     */
    private $userUnivers;

    public function __construct()
    {
        $this->userUnivers = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return parent::getId();
    }
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|UserUnivers[]
     */
    public function getUserUnivers(): Collection
    {
        return $this->userUnivers;
    }

    public function addUserUniver(UserUnivers $userUniver): self
    {
        if (!$this->userUnivers->contains($userUniver)) {
            $this->userUnivers[] = $userUniver;
            $userUniver->setUnivers($this);
        }

        return $this;
    }

    public function removeUserUniver(UserUnivers $userUniver): self
    {
        if ($this->userUnivers->contains($userUniver)) {
            $this->userUnivers->removeElement($userUniver);
            // set the owning side to null (unless already changed)
            if ($userUniver->getUnivers() === $this) {
                $userUniver->setUnivers(null);
            }
        }

        return $this;
    }
}

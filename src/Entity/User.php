<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks() 
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $updatedAt;
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Univers", mappedBy="creator")
     */
    private $univers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserUnivers", mappedBy="User")
     */
    private $userUnivers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Content", mappedBy="author")
     */
    private $contents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="toUser")
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="FromUser")
     */
    private $messageDest;

    public function __construct()
    {
        $this->roles = array('ROLE_USER');
        $this->univers = new ArrayCollection();
        $this->userUnivers = new ArrayCollection();
        $this->contents = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->messageDest = new ArrayCollection();
    }

    // other properties and methods
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function __toString(){
        return $this->username;
    }
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }
    
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
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
     * @return Collection|Univers[]
     */
    public function getUnivers(): Collection
    {
        return $this->univers;
    }

    public function addUniver(Univers $univer): self
    {
        if (!$this->univers->contains($univer)) {
            $this->univers[] = $univer;
            $univer->setCreator($this);
        }

        return $this;
    }

    public function removeUniver(Univers $univer): self
    {
        if ($this->univers->contains($univer)) {
            $this->univers->removeElement($univer);
            // set the owning side to null (unless already changed)
            if ($univer->getCreator() === $this) {
                $univer->setCreator(null);
            }
        }

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
            $userUniver->addUser($this);
        }

        return $this;
    }

    public function removeUserUniver(UserUnivers $userUniver): self
    {
        if ($this->userUnivers->contains($userUniver)) {
            $this->userUnivers->removeElement($userUniver);
            $userUniver->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|Content[]
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setAuthor($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getAuthor() === $this) {
                $content->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setToUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getToUser() === $this) {
                $message->setToUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessageDest(): Collection
    {
        return $this->messageDest;
    }

    public function addMessageDest(Message $messageDest): self
    {
        if (!$this->messageDest->contains($messageDest)) {
            $this->messageDest[] = $messageDest;
            $messageDest->setFromUser($this);
        }

        return $this;
    }

    public function removeMessageDest(Message $messageDest): self
    {
        if ($this->messageDest->contains($messageDest)) {
            $this->messageDest->removeElement($messageDest);
            // set the owning side to null (unless already changed)
            if ($messageDest->getFromUser() === $this) {
                $messageDest->setFromUser(null);
            }
        }

        return $this;
    }


}

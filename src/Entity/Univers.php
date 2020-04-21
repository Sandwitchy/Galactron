<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UniversRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Univers
{
    use DefaultObject;
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
     * @ORM\OneToMany(targetEntity="App\Entity\UserUnivers", mappedBy="Univers", orphanRemoval=true)
     */
    private $userUnivers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Content", mappedBy="univers", orphanRemoval=true)
     */
    private $contents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ContentType", mappedBy="univers", orphanRemoval=true)
     */
    private $contentTypes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrivate;

    public function __construct()
    {
        $this->userUnivers = new ArrayCollection();
        $this->contents = new ArrayCollection();
        $this->contentTypes = new ArrayCollection();
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
            $content->setUnivers($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getUnivers() === $this) {
                $content->setUnivers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ContentType[]
     */
    public function getContentTypes(): Collection
    {
        return $this->contentTypes;
    }

    public function addContentType(ContentType $contentType): self
    {
        if (!$this->contentTypes->contains($contentType)) {
            $this->contentTypes[] = $contentType;
            $contentType->setUnivers($this);
        }

        return $this;
    }

    public function removeContentType(ContentType $contentType): self
    {
        if ($this->contentTypes->contains($contentType)) {
            $this->contentTypes->removeElement($contentType);
            // set the owning side to null (unless already changed)
            if ($contentType->getUnivers() === $this) {
                $contentType->setUnivers(null);
            }
        }

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }
}

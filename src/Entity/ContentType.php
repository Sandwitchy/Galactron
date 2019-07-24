<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContentTypeRepository")
 */
class ContentType extends DefaultObject
{

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Content", mappedBy="contentType")
     */
    private $contents;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Univers", inversedBy="contentTypes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Univers;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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
            $content->setContentType($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getContentType() === $this) {
                $content->setContentType(null);
            }
        }

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

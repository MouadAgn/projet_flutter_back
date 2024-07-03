<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

      /**
     * @ORM\OneToMany(mappedBy="category", targetEntity="Leisure::class")
     * @Groups({"leisure_list"})
     */
    private Collection $leisures;

    public function __construct()
    {
        $this->leisures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Leisure>
     */
    public function getLeisures(): Collection
    {
        return $this->leisures;
    }

    public function addLeisure(Leisure $leisure): static
    {
        if (!$this->leisures->contains($leisure)) {
            $this->leisures[] = $leisure;
            $leisure->setCategory($this);
        }

        return $this;
    }

    public function removeLeisure(Leisure $leisure): static
    {
        if ($this->leisures->removeElement($leisure)) {
            // set the owning side to null (unless already changed)
            if ($leisure->getCategory() === $this) {
                $leisure->setCategory(null);
            }
        }

        return $this;
    }
}

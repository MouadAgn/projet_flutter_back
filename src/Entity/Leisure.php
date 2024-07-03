<?php

namespace App\Entity;

use App\Repository\LeisureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeisureRepository::class)]
class Leisure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $nbrPages = null;

    #[ORM\Column(length: 255)]
    private ?string $authorOrDirector = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'leisures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @ORM\ManyToOne(targetEntity="Category::class", inversedBy="leisures")
     * @ORM\JoinColumn(nullable: false)
     * @Groups({"leisure_details"})
     */

     #[ORM\OneToMany(mappedBy: 'leisure', targetEntity: Rating::class, orphanRemoval: true)]
     private Collection $ratings;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getNbrPages(): ?int
    {
        return $this->nbrPages;
    }

    public function setNbrPages(int $nbrPages): static
    {
        $this->nbrPages = $nbrPages;

        return $this;
    }

    public function getAuthorOrDirector(): ?string
    {
        return $this->authorOrDirector;
    }

    public function setAuthorOrDirector(string $authorOrDirector): static
    {
        $this->authorOrDirector = $authorOrDirector;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setLeisure($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getLeisure() === $this) {
                $rating->setLeisure(null);
            }
        }

        return $this;
    }
}

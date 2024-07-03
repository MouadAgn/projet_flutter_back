<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\ManyToOne(targetEntity: Leisure::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Leisure $leisure = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getLeisure(): ?Leisure
    {
        return $this->leisure;
    }

    public function setLeisure(?Leisure $leisure): static
    {
        $this->leisure = $leisure;

        return $this;
    }
}
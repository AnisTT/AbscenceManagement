<?php

namespace App\Entity;

use App\Repository\JustificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JustificationRepository::class)]
#[ORM\Table(name: 'justification')]
class Justification
{
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_VALIDEE = 'validee';
    public const STATUT_REFUSEE = 'refusee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pieceJointe = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSoumission = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireValidation = null;

    #[ORM\OneToOne(inversedBy: 'justification', targetEntity: Absence::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Absence $absence = null;

    #[ORM\ManyToOne]
    private ?User $validePar = null;

    public function __construct()
    {
        $this->dateSoumission = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPieceJointe(): ?string
    {
        return $this->pieceJointe;
    }

    public function setPieceJointe(?string $pieceJointe): static
    {
        $this->pieceJointe = $pieceJointe;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateSoumission(): ?\DateTimeInterface
    {
        return $this->dateSoumission;
    }

    public function setDateSoumission(\DateTimeInterface $dateSoumission): static
    {
        $this->dateSoumission = $dateSoumission;
        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
        return $this;
    }

    public function getCommentaireValidation(): ?string
    {
        return $this->commentaireValidation;
    }

    public function setCommentaireValidation(?string $commentaireValidation): static
    {
        $this->commentaireValidation = $commentaireValidation;
        return $this;
    }

    public function getAbsence(): ?Absence
    {
        return $this->absence;
    }

    public function setAbsence(?Absence $absence): static
    {
        $this->absence = $absence;
        return $this;
    }

    public function getValidePar(): ?User
    {
        return $this->validePar;
    }

    public function setValidePar(?User $validePar): static
    {
        $this->validePar = $validePar;
        return $this;
    }

    public function isEnAttente(): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE;
    }

    public function isValidee(): bool
    {
        return $this->statut === self::STATUT_VALIDEE;
    }

    public function isRefusee(): bool
    {
        return $this->statut === self::STATUT_REFUSEE;
    }

    public function valider(User $user, ?string $commentaire = null): void
    {
        $this->statut = self::STATUT_VALIDEE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTime();
        $this->commentaireValidation = $commentaire;
        
        if ($this->absence) {
            $this->absence->setJustifiee(true);
        }
    }

    public function refuser(User $user, ?string $commentaire = null): void
    {
        $this->statut = self::STATUT_REFUSEE;
        $this->validePar = $user;
        $this->dateValidation = new \DateTime();
        $this->commentaireValidation = $commentaire;
    }
}

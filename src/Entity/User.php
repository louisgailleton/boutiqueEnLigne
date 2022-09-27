<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[
        Assert\Email(message: 'l\'email {{ value }} n\'est pas valide.',),
        Assert\NotBlank
    ]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[
        Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Votre nom doit être au minimum de {{ limit }} caractères',
        maxMessage: 'Votre nom ne peut être plus long que {{ limit }} caractères',),
        Assert\Type(type: 'alpha', message: 'La valeur {{ value }} n\'est pas de type {{ type }}.'),
        Assert\NotBlank
    ]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[
        Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'Votre prénom doit être au minimum de {{ limit }} caractères',
            maxMessage: 'Votre prénom ne peut être plus long que {{ limit }} caractères',),
        Assert\Type(type: 'alpha', message: 'La valeur {{ value }} n\'est pas de type {{ type }}.'),
        Assert\NotBlank
    ]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[
        Assert\Length(
            min: 2,
            max: 255,
            minMessage: 'Votre adresse doit être au minimum de {{ limit }} caractères',
            maxMessage: 'Votre adresse ne peut être plus long que {{ 255 }} caractères',
        ),
        Assert\Type(type: 'alnum', message: 'La valeur {{ value }} n\'est pas de type {{ type }}.')
    ]
    private ?string $adresse = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[
        Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'Votre ville doit être au minimum de {{ limit }} caractères',
            maxMessage: 'Votre ville ne peut être plus long que {{ limit }} caractères',
        ),
        Assert\Type(type: 'alpha', message: 'La valeur {{ value }} n\'est pas de type {{ type }}.')
    ]
    private ?string $ville = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[
        Assert\Length(
            min: 5,
            max: 5,
            minMessage: 'Le code postal doit faire {{ limit }} chiffres',
            maxMessage: 'Le code postal doit faire {{ limit }} chiffres',
        ),
        Assert\Type(type: 'digit', message: 'La valeur {{ value }} n\'est pas de type {{ type }}.')
    ]
    private ?string $cp = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commande::class, orphanRemoval: true)]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }

        return $this;
    }
}

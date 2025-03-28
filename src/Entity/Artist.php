<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['artist:read']],
    denormalizationContext: ['groups' => ['artist:write']],
)]
class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['artist:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['artist:read', 'artist:write'])]
    private ?string $name = null; // ✅ Fixed naming

    #[ORM\Column(type: 'text')]
    #[Groups(['artist:read', 'artist:write'])]
    private ?string $description = null; // ✅ Fixed naming

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'artist', cascade: ['persist', 'remove'])]
    #[Groups(['artist:read', 'artist:write'])]
    private Collection $events;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image(
        maxSize: "3M",
        mimeTypes: ["image/jpeg", "image/png", "image/webp"],
        maxSizeMessage: "L'image est trop lourde, taille maximale autorisée: 3Mo"
    )]
    #[Groups(['artist:read', 'artist:write'])]
    private ?string $image = null; // ✅ Added serialization

    public function __construct()
    {
        $this->events = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setArtist($this); // ✅ Fixed method name
        }
        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            if ($event->getArtist() === $this) {
                $event->setArtist(null);
            }
        }
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }
}

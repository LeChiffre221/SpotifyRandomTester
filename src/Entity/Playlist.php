<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlaylistRepository")
 */
class Playlist
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\HistoTrack", mappedBy="playlist", orphanRemoval=true)
     */
    private $histoTrack;

    public function __construct()
    {
        $this->histoTrack = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
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
     * @return Collection|HistoTrack[]
     */
    public function getHistoTrack(): Collection
    {
        return $this->histoTrack;
    }

    public function addHistoTrack(HistoTrack $histoTrack): self
    {
        if (!$this->histoTrack->contains($histoTrack)) {
            $this->histoTrack[] = $histoTrack;
            $histoTrack->setPlaylist($this);
        }

        return $this;
    }

    public function removeHistoTrack(HistoTrack $histoTrack): self
    {
        if ($this->histoTrack->contains($histoTrack)) {
            $this->histoTrack->removeElement($histoTrack);
            // set the owning side to null (unless already changed)
            if ($histoTrack->getPlaylist() === $this) {
                $histoTrack->setPlaylist(null);
            }
        }

        return $this;
    }
}

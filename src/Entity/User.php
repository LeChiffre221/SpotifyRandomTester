<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
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
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $refreshToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\HistoTrack", mappedBy="user")
     */
    private $histoTrack;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Track")
     */
    private $lastTrackListened;

    public function __construct()
    {
        $this->histoTrack = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
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

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

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
            $histoTrack->setUser($this);
        }

        return $this;
    }

    public function removeHistoTrack(HistoTrack $histoTrack): self
    {
        if ($this->histoTrack->contains($histoTrack)) {
            $this->histoTrack->removeElement($histoTrack);
            // set the owning side to null (unless already changed)
            if ($histoTrack->getUser() === $this) {
                $histoTrack->setUser(null);
            }
        }

        return $this;
    }

    public function getLastTrackListened(): ?Track
    {
        return $this->lastTrackListened;
    }

    public function setLastTrackListened(?Track $lastTrackListened): self
    {
        $this->lastTrackListened = $lastTrackListened;

        return $this;
    }
}

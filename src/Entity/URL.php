<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\URLRepository;

/**
 * @ORM\Table(name="urls")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass=URLRepository::class)
 */
class URL
{

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESS = 'process';
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'success';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\PreUpdate()
     * @return void
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PrePersist()
     * @return void
     */
    public function onPrePersist()
    {
        $this->updatedAt = new DateTime();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

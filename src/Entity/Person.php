<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    private $gender;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPositionId(): ?string
    {
        return $this->position_id;
    }

    public function setPositionId(?int $position_id): self
    {
        $this->position_id = $position_id;
        return $this;
    }
}

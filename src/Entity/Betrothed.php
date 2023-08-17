<?php

namespace App\Entity;

use App\Repository\BetrothedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Asserts;

#[ORM\Entity(repositoryClass: BetrothedRepository::class)]
class Betrothed extends User
{
    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $email = null;

    /**
     * @var Betrothed
     */
    #[ORM\OneToOne(targetEntity: Betrothed::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private Betrothed $betrothed;

    public const DEFAULT_ROLES = [
        'ROLE_BETROTHED',
        'ROLE_USER'
    ];

    public function __construct()
    {
        $this->isDeleted = false;
        $this->isVerified = false;

        parent::__construct();
    }

    /**
     * @param Betrothed $betrothed
     * @return $this
     */
    public function setBetrothed( Betrothed $betrothed ): self
    {
        $this->betrothed = $betrothed;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return Betrothed
     */
    public function getBetrothed(): Betrothed
    {
        return $this->betrothed;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}

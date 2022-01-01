<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method string getUserIdentifier()
 */
#[Entity(repositoryClass: UserRepository::class, readOnly: false)]
#[ApiResource(shortName: 'user')]
class User implements UserInterface
{
    /** @param array<string, string> $data */
    public function __construct(array $data = [])
    {
        $this->twitchUsername = $data['login'];
        $this->twitchId = $data['id'];
        $this->twitchEmail= $data['email'];
    }

    #[
        Id,
        Column(name: 'id', type: 'integer', nullable: false),
        GeneratedValue(strategy: 'IDENTITY'),
    ]
    private int $id;

    #[Column(name: 'twitch_email', type: 'string', nullable: false)]
    private string $twitchEmail;

    #[Column(name: 'twitch_id', type: 'string', nullable: true)]
    private string $twitchId;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $twitchUsername;

    #[Column(name: 'twitch_access_token', type: 'string', nullable: true)]
    private ?string $twitchAccessToken;

    #[Column(name: 'twitch_refresh_token', type: 'string', nullable: true)]
    private ?string $twitchRefreshToken;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTwitchEmail(): string
    {
        return $this->twitchEmail;
    }

    public function setTwitchEmail(string $twitchEmail): void
    {
        $this->twitchEmail = $twitchEmail;
    }

    public function getTwitchId(): string
    {
        return $this->twitchId;
    }

    public function setTwitchId(mixed $twitchId): void
    {
        $this->twitchId = $twitchId;
    }

    public function getTwitchUsername(): string
    {
        return $this->twitchUsername;
    }

    public function setTwitchUsername(mixed $twitchUsername): void
    {
        $this->twitchUsername = $twitchUsername;
    }

    public function getTwitchAccessToken(): ?string
    {
        return $this->twitchAccessToken;
    }

    public function setTwitchAccessToken(?string $twitchAccessToken): void
    {
        $this->twitchAccessToken = $twitchAccessToken;
    }

    public function getTwitchRefreshToken(): ?string
    {
        return $this->twitchRefreshToken;
    }

    public function setTwitchRefreshToken(?string $twitchRefreshToken): void
    {
        $this->twitchRefreshToken = $twitchRefreshToken;
    }

    public function getRoles()
    {
        return array_unique(['ROLE_USER']);
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername(): string
    {
        return $this->twitchUsername;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }
}
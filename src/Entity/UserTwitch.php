<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Security\Core\User\UserInterface;

#[Entity(repositoryClass: UserRepository::class, readOnly: false)]
#[ApiResource(shortName: 'user')]
class UserTwitch implements UserInterface
{
    /** @param array<string, string> $data */
    public function __construct(array $data = [])
    {
        $this->email    = $data['email'];
        $this->login    = $data['login'];
        $this->twitchId = $data['id'];
        $this->username = $data['display_name'];
    }

    #[
        Id,
        Column(name: 'id', type: 'integer', nullable: false),
        GeneratedValue(strategy: 'IDENTITY'),
    ]
    private int $id;

    #[Column(name: 'twitch_id', type: 'string', nullable: true)]
    private string $twitchId;

    #[Column(name: 'twitch_login', type: 'string', nullable: true)]
    private string $login;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $username;

    #[Column(name: 'twitch_email', type: 'string', nullable: false)]
    private string $email;

    #[Column(name: 'twitch_access_token', type: 'string', nullable: true)]
    private ?string $accessToken;

    #[Column(name: 'twitch_refresh_token', type: 'string', nullable: true)]
    private ?string $refreshToken;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTwitchId(): mixed
    {
        return $this->twitchId;
    }

    public function setTwitchId(mixed $twitchId): UserTwitch
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function getLogin(): mixed
    {
        return $this->login;
    }

    public function setLogin(mixed $login): UserTwitch
    {
        $this->login = $login;
        return $this;
    }

    public function getUsername(): mixed
    {
        return $this->username;
    }

    public function setUsername(mixed $username): UserTwitch
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): mixed
    {
        return $this->email;
    }

    public function setEmail(mixed $email): UserTwitch
    {
        $this->email = $email;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): UserTwitch
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): UserTwitch
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /** @return array<int, string> */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
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
}

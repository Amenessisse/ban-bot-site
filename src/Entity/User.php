<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Security\Core\User\UserInterface;

#[Entity(repositoryClass: UserRepository::class, readOnly: false)]
#[ApiResource(shortName: 'user')]
class User implements UserInterface
{
    public function __construct(array $data = [])
    {
        $this->twitchUsername = $data['login'];
        $this->twitchId = $data['user_id'];
    }

    #[
        Id,
        Column(name: 'id', type: 'integer', nullable: false),
        GeneratedValue(strategy: 'IDENTITY'),
    ]
    private int $id;

    #[Column(name: 'twitch_id', type: 'string', nullable: true)]
    private string $twitchId;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $twitchUsername;

    #[Column(name: 'twitch_access_token', type: 'string', nullable: true)]
    private ?string $twitchAccessToken;

    #[Column(name: 'email', type: 'string', nullable: false)]
    private ?string $email;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTwitchId(): string
    {
        return $this->twitchId;
    }

    /**
     * @param string $twitchId
     * @return User
     */
    public function setTwitchId(string $twitchId): User
    {
        $this->twitchId = $twitchId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTwitchAccessToken(): ?string
    {
        return $this->twitchAccessToken;
    }

    /**
     * @param string|null $twitchAccessToken
     * @return User
     */
    public function setTwitchAccessToken(?string $twitchAccessToken): User
    {
        $this->twitchAccessToken = $twitchAccessToken;
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
     * @param string|null $email
     * @return User
     */
    public function setEmail(?string $email): User
    {
        $this->email = $email;
        return $this;
    }
}
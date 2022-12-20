<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
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
//        $this->twitchId = $data['id'];
        $this->username = $data['display_name'];
        $this->bans = new ArrayCollection();
        $this->broadcasters = new ArrayCollection();
    }

    // TODO C'est quoi la taille des id twitch ? Mettre une taille car sinon varchar 255 partout
    #[
        Id,
        Column(name: 'id', type: 'string', nullable: false),
        GeneratedValue(strategy: 'IDENTITY'),
    ]
    private int $id;

//    #[ManyToMany(targetEntity: BannedUser::class, mappedBy: 'twitchUsers')]
//    #[JoinTable('twitch_users_banned_users')]
//    /** @var array<int, BannedUser> $bannedUsers */
//    private array $bannedUsers;

    #[Column(name: 'twitch_id', type: 'string', nullable: true)]
    private string $twitchId;

    #[Column(name: 'twitch_login', type: 'string', nullable: true)]
    private string $login;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $username;

    // TODO par défault en false
    #[Column(name: 'twitch_email', type: 'string', nullable: false)]
    private string $email;

    #[Column(name: 'twitch_access_token', type: 'string', nullable: true)]
    private string|null $accessToken;

    #[Column(name: 'twitch_refresh_token', type: 'string', nullable: true)]
    private string|null $refreshToken;

    #[Column(name: 'expires_in', type: 'datetime', nullable: false)]
    private DateTime $expiresIn;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UserTwitch
    {
        $this->id = $id;

        return $this;
    }

//    public function getTwitchId(): string
//    {
//        return $this->twitchId;
//    }
//
//    public function setTwitchId(string $twitchId): UserTwitch
//    {
//        $this->twitchId = $twitchId;
//
//        return $this;
//    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): UserTwitch
    {
        $this->login = $login;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): UserTwitch
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserTwitch
    {
        $this->email = $email;

        return $this;
    }

    public function getAccessToken(): string|null
    {
        return $this->accessToken;
    }

    public function setAccessToken(string|null $accessToken): UserTwitch
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): string|null
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string|null $refreshToken): UserTwitch
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getExpiresIn(): DateTime
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(DateTime $expiresIn): UserTwitch
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

//    /**
//     * @return array
//     */
//    public function getBannedUsers(): array
//    {
//        return $this->bannedUsers;
//    }
//
//    /**
//     * @param array $bannedUsers
//     * @return UserTwitch
//     */
//    public function setBannedUsers(array $bannedUsers): UserTwitch
//    {
//        $this->bannedUsers = $bannedUsers;
//        return $this;
//    }



    /** @return array<int, string> */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): void
    {
        // TODO: Implement getPassword() method.
    }

    public function getSalt(): void
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }
}

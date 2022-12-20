<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TwitchBanRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: TwitchBanRepository::class, readOnly: false)]
#[ApiResource(shortName: 'bannedUser')]
class TwitchBan
{
//    #[
//        Id,
//        Column(name: 'id', type: 'integer', nullable: false),
//        GeneratedValue(strategy: 'IDENTITY'),
//    ]
//    private int $id;

//    #[ManyToMany(targetEntity: UserTwitch::class, inversedBy: 'bannedUsers')]
//    /** @var array<int, UserTwitch> $twitchUsers */
//    private array $twitchUsers;

//    #[ManyToOne(targetEntity: BlackList::class, inversedBy: 'bannedUsers')]
//    private BlackList $blackList;

    #[Id, ManyToOne(targetEntity: TwitchUser::class)]
    private TwitchUser $user;

    #[Id, ManyToOne(targetEntity: TwitchUser::class)]
    private TwitchUser $broadcaster;

//    #[Column(name: 'twitch_id', type: 'string', nullable: false)]
//    private string $twitchId;

    #[Column(name: 'twitch_login', type: 'string', nullable: true)]
    private string $login;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $username;

    // TODO IMPORTANT pour faire attention aux bans
    // Nul à chier, se calcul avec Broadcaster.DateCreation - TwitchBan.DateCreationDETWITCH_ETPASDEDOCTRINE
    // private DateTime $twitchUserCreatedAt;


//    public function getTwitchId(): string
//    {
//        return $this->twitchId;
//    }
//
//    public function setTwitchId(string $twitchId): BannedUser
//    {
//        $this->twitchId = $twitchId;
//
//        return $this;
//    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): TwitchBan
    {
        $this->login = $login;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): TwitchBan
    {
        $this->username = $username;

        return $this;
    }

//    /**
//     * @return array
//     */
//    public function getTwitchUsers(): array
//    {
//        return $this->twitchUsers;
//    }
//
//    /**
//     * @param array $twitchUsers
//     * @return BannedUser
//     */
//    public function setTwitchUsers(array $twitchUsers): BannedUser
//    {
//        $this->twitchUsers = $twitchUsers;
//        return $this;
//    }
//
//    public function getBlackList(): BlackList
//    {
//        return $this->blackList;
//    }
//
//    public function setBlackList(BlackList $blackList): BannedUser
//    {
//        $this->blackList = $blackList;
//
//        return $this;
//    }

    public function getBroadcaster(): TwitchUser
    {
        return $this->broadcaster;
    }

    public function setBroadcaster(TwitchUser $broadcaster): void
    {
        $this->broadcaster = $broadcaster;
    }

    public function getUser(): TwitchUser
    {
        return $this->user;
    }

    public function setUser(TwitchUser $user): void
    {
        $this->user = $user;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BannedUserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: BannedUserRepository::class, readOnly: false)]
#[ApiResource(shortName: 'bannedUser')]
class BannedUser
{
    #[
        Id,
        Column(name: 'id', type: 'integer', nullable: false),
        GeneratedValue(strategy: 'IDENTITY'),
    ]
    private int $id;

    #[OneToMany(mappedBy: 'bannedUser', targetEntity: UserTwitch::class)]
    private UserTwitch $twitchUsers;

    #[Column(name: 'twitch_id', type: 'string', nullable: false)]
    private string $twitchId;

    #[Column(name: 'twitch_login', type: 'string', nullable: true)]
    private string $login;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $username;

    #[Column(name: 'counter', type: 'integer', nullable: false)]
    private int $counter;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): BannedUser
    {
        $this->id = $id;

        return $this;
    }

    public function getTwitchId(): string
    {
        return $this->twitchId;
    }

    public function setTwitchId(string $twitchId): BannedUser
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): BannedUser
    {
        $this->login = $login;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): BannedUser
    {
        $this->username = $username;

        return $this;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): BannedUser
    {
        $this->counter = $counter;

        return $this;
    }

    public function getTwitchUsers(): UserTwitch
    {
        return $this->twitchUsers;
    }

    public function setTwitchUsers(UserTwitch $twitchUsers): BannedUser
    {
        $this->twitchUsers = $twitchUsers;

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BannedUserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

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

    #[Column(name: 'twitch_id', type: 'integer', nullable: false)]
    private int $twitchId;

    #[Column(name: 'twitch_login', type: 'string', nullable: true)]
    private string $login;

    #[Column(name: 'twitch_username', type: 'string', nullable: true)]
    private string $username;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): BannedUser
    {
        $this->id = $id;
        return $this;
    }

    public function getTwitchId(): int
    {
        return $this->twitchId;
    }

    public function setTwitchId(int $twitchId): BannedUser
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
}

<?php

namespace App\Domain\Entity;

class User extends Entity implements \JsonSerializable
{
    protected $email;
    protected $password;
    protected $activated = false;
    protected $deleted = false;
    protected $memberSince;
    protected $roles;

    public function __construct(
        ?int $id,
        string $email,
        array $roles
    ){
        $this->setId($id);
        $this->email = $email;
        $this->roles = $roles;
        $this->memberSince = new \DateTime();
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getActivated() : bool
    {
        return $this->activated;
    }

    public function setActivated($active): void
    {
        $this->activated = $active;
    }

    public function getDeleted() : bool
    {
        return $this->deleted;
    }

    public function setDeleted($deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getMemberSince() : \DateTime
    {
        return $this->memberSince;
    }

    public function setMemberSince($memberSince): void
    {
        $this->memberSince = $memberSince;
    }

    public function getRoles() : array
    {
        foreach ($this->roles as $role) {
            if (is_object($role)) {
                $_roles[] = $role->getTitle();
            }
            else{
                $_roles[] = $role;
            }
        }
        return $_roles;
    }

    public function setRoles($roles) : void
    {
        $this->roles = $roles;
    }

    public function equals(self $user): bool
    {
        return
            $this->getId() === $user->getId() &&
            $this->getEmail() === $user->getEmail() &&
            $this->getActivated() === $user->getActivated() &&
            $this->getDeleted() === $user->getDeleted() &&
            $this->getMemberSince() === $user->getMemberSince();
    }

    public function getUsername() : string
    {
        return $this->getEmail();
    }

    public function jsonSerialize() : array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'activated' => $this->getActivated(),
            'memberSince' => $this->getMemberSince()
        ];
    }
}

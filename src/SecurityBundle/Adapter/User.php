<?php

namespace AppGear\SecurityBundle\Adapter;

use AppGear\SecurityBundle\Entity\User as UserEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface
{
    /**
     * User entity
     *
     * @var UserEntity
     */
    private $user;

    /**
     * Constructor
     *
     * @param UserEntity $user User entity
     */
    public function __construct(UserEntity $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->user->getPassword();
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->user->getPassword() !== $user->getPassword()) {
            return false;
        }

        if (null !== $user->getSalt()) {
            return false;
        }

        if ($this->user->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
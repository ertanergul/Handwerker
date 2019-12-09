<?php

namespace App\Usecase;

use App\Domain\Entity\User;
use App\Domain\Repository\User\UserSaver;
use App\Domain\Security\Role;
use App\Domain\Service\PasswordEncoder;
use App\Domain\Service\Validator;
use App\Usecase\Command\RegisterUserCommand;

class RegisterUserHandler
{
    private PasswordEncoder $passwordEncoder;
    private Validator $validator;
    private UserSaver $userSaver;

    public function __construct(
        PasswordEncoder $passwordEncoder,
        Validator $validator,
        UserSaver $userSaver
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->userSaver = $userSaver;
    }

    public function handle(RegisterUserCommand $command): void
    {
        $this->validator->validate($command);

        $user = new User();
        $user->setUuid($command->getUuid());
        $user->setEmail($command->getEmail());

        $encodedPassword = $this->passwordEncoder->encode(
            $command->getPassword(),
            $user->getSalt()
        );

        $user->setPassword($encodedPassword);

        $user->setRoles([Role::USER]);

        $this->userSaver->save($user);
    }
}

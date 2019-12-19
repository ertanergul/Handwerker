<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\CommandHandler;

use App\Application\Command\RegisterUserCommand;
use App\Application\CommandHandler\RegisterUserHandler;
use App\Application\Service\PasswordEncoder;
use App\Application\Service\Uuid;
use App\Application\Service\Validator;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\User\UserSaver;
use App\Tests\Shared\KernelTestCase;
use App\Tests\Shared\TestData;

class RegisterUserHandlerTest extends KernelTestCase
{
    public const EMAIL = 'registerUserHanlderTest@eresdev.com';
    public const PASSWORD = 'SomeRandomPassword2348';
    private PasswordEncoder $passwordEncoder;
    private Validator $validator;
    private UserSaver $userSaver;
    private Uuid $uuidGenerator;

    protected function setUp()
    {
        static::bootKernel();
        parent::setUp();

        $this->passwordEncoder = $this->getService(PasswordEncoder::class);
        $this->validator = $this->getService(Validator::class);
        $this->userSaver =
            $this->createMock(UserSaver::class);
        $this->uuidGenerator = $this->getService(Uuid::class);
    }

    public function testHandleWithValidData(): void
    {
        $this->userSaver
            ->expects($this->once())
            ->method('save');

        $command = new RegisterUserCommand(
            $this->uuidGenerator->generate(),
            self::EMAIL,
            self::PASSWORD
        );

        $handler = new RegisterUserHandler(
            $this->passwordEncoder,
            $this->validator,
            $this->userSaver
        );

        $handler->handle($command);
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testHandleWithInValidEmail(TestData $testData): void
    {
        $this->expectException(ValidationException::class);

        $command = new RegisterUserCommand(
            $this->uuidGenerator->generate(),
            $testData->getInput()['email'],
            $testData->getInput()['password']
        );

        $handler = new RegisterUserHandler(
            $this->passwordEncoder,
            $this->validator,
            $this->userSaver
        );

        try {
            $handler->handle($command);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($testData->getExpectedValue(), $exception->getMessagesForEndUser()[0]);
            throw $exception; // so that $this->expectException passes
        }
    }

    public function getInvalidValues() : array
    {
        return [
            [
                new TestData(
                    ['email' => '', 'password' => self::PASSWORD],
                    'email',
                    'Validation problem: Given empty email but did not get back email validation error'
                )
            ],
            [
                new TestData(
                    ['email' => 'invalidEmail', 'password' => self::PASSWORD],
                    'email',
                    'Validation problem: Given invalid email but did not get back email validation error'
                )
            ],
            [
                new TestData(
                    ['email' => str_repeat("a", 61) . '.eresdev.com', 'password' => self::PASSWORD],
                    'email',
                    'Validation problem: Given too long email but did not get back email validation error'
                )
            ],
            [
                new TestData(
                    ['email' => self::EMAIL, 'password' => '', 'expectedInvalidField' => 'password'],
                    'password',
                    'Validation problem: Given empty password but did not get back password validation error'
                )
            ],
            [
                new TestData(
                    ['email' => self::EMAIL, 'password' => 'foo'],
                    'password',
                    'Validation problem: Given too short password but did not get back password validation error'
                )
            ],
            [
                new TestData(
                    ['email' => self::EMAIL, 'password' => str_repeat("a", 4099)],
                    'password',
                    'Validation problem: Given too long but did not get back password validation error'
                )
            ]
        ];
    }
}

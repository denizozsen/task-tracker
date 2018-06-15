<?php

namespace App\Service;

use App\Exceptions\UserFacingException;
use App\Mail\AccountVerification;
use App\Mail\Invite;
use App\Repository\UserRepository;
use App\Repository\VerificationRepository;
use App\User;
use App\Verification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;

/**
 * Provides functionality concerned with user registration.
 */
class RegistrationService
{
    /** @var UserRepository */
    private $userRepository;

    /** @var VerificationRepository */
    private $verificationRepository;

    public function __construct(UserRepository $userRepository, VerificationRepository $verificationRepository)
    {
        $this->userRepository         = $userRepository;
        $this->verificationRepository = $verificationRepository;
    }

    /**
     * Registers a new user on the system with the given user data. The following steps are taken:
     *
     * 1. Save user data in users table
     * 2. Generate unique verification code, save in verifications table
     * 3. Send mail to user, asking them to enter their verification code
     *
     * @throws UserFacingException if registration fails, for some reason
     */
    public function register(array $newUserData): User
    {
        // Populate model with submitted data
        $newUser = $this->userRepository->createNew($newUserData);

        // Save new model to db
        try {
            $newUser->save();
        } catch (QueryException $e) {
            $errorMessage = 'Saving new user to database failed.';
            if ($e->errorInfo[1] == 1062) {
                $errorMessage = 'User with this email already exists.';
            }
            throw new UserFacingException($errorMessage);
        }

        // Fetch newly created user from db, to ensure we get any attributes generated on the db side
        $createdUser = $this->userRepository->getFirst(['id' => $newUser->id]);

        $this->doSendVerificationCode($createdUser);

        return $createdUser;
    }

    /**
     * Resends a verification code for the given email.
     *
     * @throws UserFacingException
     */
    public function resendVerificationCode(string $email): void
    {
        try {
            $user = $this->userRepository->getFirst(['email' => $email]);
        } catch(\Exception $e) {
            throw new UserFacingException("No user found with email {$email}");
        }

        $this->doSendVerificationCode($user);
    }

    /**
     * Checks if the given email/code combination is valid and, if so, sets the corresponding user as verified.
     *
     * @throws UserFacingException if the given user/code combination is invalid
     */
    public function setVerified(string $email, string $verificationCode): void
    {
        try {
            $user = $this->userRepository->getFirst(['email' => $email]);
            $verification = $this->verificationRepository->getFirst([
                'user' => $user->id,
                'code' => $verificationCode,
            ]);
            $verification->done = 1;
            $verification->saveOrFail();
        } catch(\Throwable $e) {
            throw new UserFacingException('Invalid email or verification code');
        }
    }

    /**
     * Sends an invite mail to the given email, asking the person to complete the registration.
     *
     * @throws UserFacingException
     */
    public function invite(string $email, int $senderUserId): void
    {
        $sender = $this->userRepository->getById($senderUserId);
        if ($sender->role != User::ROLE_ADMIN) {
            throw new UserFacingException('Only admins can send invites');
        }

        try {
            $existingUser = $this->userRepository->getFirst(['email' => $email]);
            if ($existingUser) {
                throw new UserFacingException("There is already a Task Tracker account associated with {$email}");
            }
        } catch (ModelNotFoundException $exception) {
            // Ignoring exception, because not finding a user model is what we want here.
        }

        try {
            // Send verification mail to new user
            Mail::to($email)->send(new Invite($sender));
        } catch (\Throwable $e) {
            throw new UserFacingException('Failed sending invite mail.');
        }

    }

    /**
     * Generates a unique alpha-numeric string that is 6 characters in length.
     */
    private function generateVerificationCode(): string
    {
        return substr(hexdec(bin2hex(random_bytes(3))), 0, 6);
    }

    /**
     * Generates and sends a verification code for the given user.
     *
     * @throws UserFacingException
     */
    private function doSendVerificationCode(User $user): void
    {
        $verification = null;
        try {
            $verification = $this->verificationRepository->getFirst(['user' => $user->id]);
        } catch (ModelNotFoundException $exception) {
            $verification = new Verification(['user' => $user->id]);
        }

        // Generate verification code and save it to db
        $verification->code = $this->generateVerificationCode();
        $verification->done = 0;
        try {
            $verification->saveOrFail();
        } catch (\Throwable $e) {
            $errorMessage = 'Generating verification code failed.';
            throw new UserFacingException($errorMessage);
        }

        // Send verification mail to new user
        Mail::to($user->email)->send(new AccountVerification($user, $verification));
    }
}

<?php

namespace App\Service;

use App\Exceptions\AccountBlockedException;
use App\Exceptions\InvalidEmailOrPasswordException;
use App\Exceptions\NotAuthenticatedException;
use App\Exceptions\NotVerifiedException;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Session;
use App\User;
use App\Verification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

/**
 * Provides functionality concerned with logging users in and out of the system.
 */
class LoginService
{
    /** @var int session timeout, in minutes */
    const SESSION_TIMEOUT = 15;

    /**
     * @var int when a user fails to log in this many times, in a row,
     * their account is blocked, and can only be unblocked by an admin.
     */
    const BLOCK_THRESHOLD = 3;

    /** @var UserRepository */
    private $userRepository;

    /** @var SessionRepository */
    private $sessionRepository;

    public function __construct(UserRepository $userRepository, SessionRepository $sessionRepository)
    {
        $this->userRepository    = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * Checks the given login credentials. Does not return anything, but throws exceptions on failure or error. If
     * the method returns, without throwing an exceptions, it can be assumed that the login credentials are correct.
     *
     * @throws NotVerifiedException if login fails, because verification has not been done
     * @throws InvalidEmailOrPasswordException if login specified wrong email or password
     * @throws \Exception if login fails, for some other reason
     */
    public function checkCredentials(string $email, string $password): void
    {
        // Find user record with given email
        try {
            $user = $this->userRepository->getFirst(['email' => $email]);
        } catch(ModelNotFoundException $e) {
            throw new InvalidEmailOrPasswordException();
        }

        $this->checkPassword($user, $password);
        $this->checkAccountVerified($user);
        $this->checkBlockThresholdExceeded($user);
        $this->resetLoginFailCount($user);
        $this->rehashPassword($user, $password);
    }

    /**
     * Creates a new login session and returns the session information required by the client.
     *
     * @throws \Throwable if something goes wrong while creating the session
     */
    public function createSession(string $email): array
    {
        // Find user record with given email
        /** @var User $user */
        $user = $this->userRepository->getFirst(['email' => $email]);

        // Create new session token
        $token = bin2hex(random_bytes(6));

        // Persist session info in db
        $session = new Session([
            'user' => $user->id,
            'token' => $token,
        ]);
        $session->saveOrFail();

        // session info structure
        $sessionInfo = $this->createSessionInfoStructure($user, $token);

        return $sessionInfo;
    }

    /**
     * Retrieves the login session information for the given session token.
     *
     * @throws \Throwable if something goes wrong while creating the session
     */
    public function getSession(string $token): array
    {
        /** @var Session $session */
        $session = $this->sessionRepository->getFirst(['token' => $token]);

        /** @var User $user */
        $user = $this->userRepository->getById($session->user);

        $sessionInfo = $this->createSessionInfoStructure($user, $token);

        return $sessionInfo;
    }

    /**
     * Ensures that the given authorization value (usually passed from client to server via Authorization header) is
     * a valid bearer token value, and that the user associated with that token has access to resources owned by the
     * given resource user.
     * The following rules are enforced:
     *
     * (1) Authorization value must be prefixed by keyword 'Bearer'
     * (2) Session associated with authorization value has not yet expired
     * (3) Session associated with authorization value has permissions over resources owner by given resource user id
     *
     * @throws NotAuthenticatedException if client is not authenticated, based on given user id and HMAC
     * @throws \Exception on other errors
     */
    public function ensureSessionAuthenticated(string $authorizationValue, int $resourceUserId)
    {
        if (strpos($authorizationValue, 'Bearer') === false) {
            throw new NotAuthenticatedException();
        }
        $token = trim(str_replace('Bearer', '', $authorizationValue));

        try {
            /** @var Session $session */
            $session = $this->sessionRepository->getFirst(['token' => $token]);
        } catch(ModelNotFoundException $e) {
            throw new NotAuthenticatedException();

        }

        // Check if this session is already expired
        $expiry = (new \DateTime($session->updated_at))->modify('+ ' . self::SESSION_TIMEOUT . ' minutes');
        if ($expiry->getTimestamp() < time()) {
            $session->delete();
            throw new NotAuthenticatedException();
        }

        // Check if user id in request matches user id stored for the session
        $this->ensureRoleBasedPermissions($session->user, $resourceUserId);

        // Update session timestamp
        $session->touch();
        $session->save();
    }

    /**
     * Ensures that the given acting user has permissions over resources from the given resource user.
     *
     * @throws ModelNotFoundException if either of the users are not found
     * @throws NotAuthenticatedException if $actingUserId has no permissions over $resourceUserId
     * @throws \Exception on invalid user role
     */
    private function ensureRoleBasedPermissions(int $actingUserId, int $resourceUserId): void
    {
        $actingUser   = $this->userRepository->getById($actingUserId);
        $resourceUser = $this->userRepository->getById($resourceUserId);

        switch ($actingUser->role) {
            case User::ROLE_STANDARD:
                if ($actingUserId != $resourceUserId) {
                    throw new NotAuthenticatedException();
                }
                break;
            case User::ROLE_MANAGER:
                if ($actingUserId != $resourceUserId && $resourceUser->role != User::ROLE_STANDARD) {
                    throw new NotAuthenticatedException();
                }
                break;
            case User::ROLE_ADMIN:
                // No check here, because admins have permissions to everything
                break;
            default:
                throw new \Exception("Invalid user role: {$actingUser->role}");
        }
    }

    private function createSessionInfoStructure(User $user, string $token): array
    {
        // Get managed users
        $managedUsers = [];
        if ($user->role == 'admin') {
            $managedUsers = $this->userRepository->getAll('id', '<>', $user->id);
        } elseif ($user->role == 'manager') {
            $managedUsers = $this->userRepository->getAllMultipleCriteria([
                ['id'  , '<>', $user->id],
                ['role', '=' , 'standard']
            ]);
        }

        return [
            'user'          => $user,
            'token'         => $token,
            'managed_users' => $managedUsers,
        ];
    }

    /**
     * @throws InvalidEmailOrPasswordException
     */
    private function checkPassword(User $user, string $password): void
    {
        if (!Hash::check($password, $user->password)) {
            $user->login_fail_count = (int)$user->login_fail_count + 1;
            $user->save();
            throw new InvalidEmailOrPasswordException();
        }
    }

    /**
     * @throws AccountBlockedException
     */
    private function checkBlockThresholdExceeded(User $user): void
    {
        if ((int)$user->login_fail_count >= self::BLOCK_THRESHOLD) {
            throw new AccountBlockedException();
        }
    }

    /**
     * @throws NotVerifiedException
     */
    private function checkAccountVerified(User $user): void
    {
        $verification = Verification::query()->where(['user' => $user->id])->first();
        if (!$verification || !$verification->done) {
            throw new NotVerifiedException();
        }
    }

    private function resetLoginFailCount(User $user): void
    {
        $user->login_fail_count = 0;
        $user->save();
    }

    private function rehashPassword(User $user, string $password): void
    {
        if (Hash::needsRehash($user->password)) {
            $user->password = $password;
            $user->save();
        }
    }
}

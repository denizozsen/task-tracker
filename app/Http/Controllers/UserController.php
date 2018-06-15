<?php

namespace App\Http\Controllers;

use App\Exceptions\UserFacingException;
use App\Repository\UserRepository;
use App\Service\LoginService;
use App\Service\RegistrationService;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic;

class UserController extends Controller
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Creates a new User.
     *
     * @throws UserFacingException
     */
    public function create(RegistrationService $registrationService, Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|max:255',
            'email'    => 'required|max:255|email',
            'password' => 'required|max:255',
        ]);

        $submittedData = $request->all();
        $user = $registrationService->register($submittedData);
        return $this->jsonResponse([ 'user' => $user ], 201);
    }

    /**
     * Retrieves the specified User.
     *
     * @throws UserFacingException
     */
    public function get(int $userId): JsonResponse
    {
        try {
            $user = $this->userRepository->getFirst(['id' => $userId]);
            return $this->jsonResponse([ 'user' => $user ]);
        } catch (\Exception $e) {
            throw new UserFacingException("Failed to retrieve user with id {$userId}");
        }
    }

    /**
     * Updates the specified User.
     *
     * @throws UserFacingException
     */
    public function update(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'name'             => 'required|max:255',
            'email'            => 'required|max:255|email',
            'password'         => 'max:255',
            'picture'          => 'max:255',
            'role'             => 'in:standard,manager,admin',
            'login_fail_count' => 'integer',
        ]);

        try {
            $user = $this->userRepository->getFirst(['id' => $userId]);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if ($request->has('password')) {
                $user->password = $request->input('password');
            }
            if ($request->has('picture')) {
                $user->picture = $request->input('picture');
            }
            if ($request->has('role')) {
                // TODO - check if session user is allowed to change role to that value
                $user->role = $request->input('role');
            }
            if ($request->has('login_fail_count')) {
                $user->login_fail_count = $request->input('login_fail_count');
            }
            $user->save();
            return $this->jsonResponse(['user' => $user]);
        } catch (\Exception $e) {
            throw new UserFacingException("Failed to update user with id {$userId}");
        }
    }

    /**
     * Deletes the specified User.
     *
     * @throws UserFacingException
     */
    public function delete(int $userId): JsonResponse
    {
        try {
            // TODO - consider doing a logical delete, e.g. setting a 'deleted' flag to 1
            $this->userRepository->getById($userId)->delete();
            return $this->jsonResponse(['id' => $userId]);
        } catch (\Exception $e) {
            throw new UserFacingException("Failed to delete user with id {$userId}");
        }
    }

    /**
     * Attempts to log in a user with the given credentials.
     *
     * @throws UserFacingException
     * @throws \Throwable
     */
    public function login(LoginService $loginService, Request $request)
    {
        $request->validate([
            'email'    => 'required|max:255|email',
            'password' => 'required|max:255',
        ]);

        $email    = $request->post('email');
        $password = $request->post('password');

        $loginService->checkCredentials($email, $password);
        $session = $loginService->createSession($email);

        return $this->jsonResponse(['sessionInfo' => $session], 201);
    }

    /**
     * Retrieves the login session information for the token given in the Authorization header.
     *
     * @throws \Throwable
     */
    public function getSession(LoginService $loginService, Request $request)
    {
        $authorizationValue = $request->header('Authorization');
        $token = trim(str_replace('Bearer', '', $authorizationValue));
        $session = $loginService->getSession($token);

        return $this->jsonResponse(['sessionInfo' => $session], 201);
    }

    /**
     * Checks the submitted verification code (via 'code' post param) and sets the verified flag, if valid.
     *
     * @throws UserFacingException
     * @throws \Throwable
     */
    public function setVerified(RegistrationService $registrationService, LoginService $loginService, Request $request): JsonResponse
    {
        $request->validate([
            'email'            => 'required|max:255|email',
            'verificationCode' => 'required|max:255|digits:6',
        ]);

        $email            = $request->post('email');
        $verificationCode = $request->post('verificationCode');

        $registrationService->setVerified($email, $verificationCode);

        $sessionInfo = $loginService->createSession($email);
        return $this->jsonResponse(['sessionInfo' => $sessionInfo]);
    }

    /**
     * Sends a new verification code to the given email.
     *
     * @throws UserFacingException
     */
    public function resendVerificationCode(RegistrationService $registrationService, Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|max:255|email'
        ]);

        $email = $request->post('email');
        $registrationService->resendVerificationCode($email);

        return $this->jsonResponse([], 201);
    }

    /**
     * Sends a registration invite to the given email.
     *
     * @throws UserFacingException
     */
    public function invite(RegistrationService $registrationService, Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|max:255|email'
        ]);

        $email        = $request->input('email');
        $senderUserId = $request->route('userId');

        $sender = $this->userRepository->getById($senderUserId);
        if ($sender->role != User::ROLE_ADMIN) {
            throw new UserFacingException('Only admins can send invites');
        }

        $registrationService->invite($email, $senderUserId);

        return $this->jsonResponse([], 201);
    }

    /**
     * Uploads profile picture file.
     *
     * @throws UserFacingException
     */
    public function uploadPicture(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'image'
        ]);

        $file   = $request->file('file');
        $userId = $request->route('userId');

        $fileName = $file->getClientOriginalName();

        $targetDir = storage_path('app/public/user/' . $userId);
        $targetPath = $targetDir .  '/' . $fileName;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageResize = ImageManagerStatic::make($file->getRealPath());
        $imageResize->resize(120, 120);
        $imageResize->save($targetPath);

        return $this->jsonResponse([], 201);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exceptions\UserFacingException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function callAction($method, $parameters): \Symfony\Component\HttpFoundation\Response
    {
        try {
            return parent::callAction($method, $parameters);
        } catch (ValidationException $e) {
            $validationErrors = $e->validator->getMessageBag()->getMessages();
            return $this->failureJsonResponse($e->getMessage(), 'validation_failed', $validationErrors, 400);
        } catch (UserFacingException $e) {
            return $this->failureJsonResponse($e->getMessage(), $e->getType(), [], $e->getHttpStatus());
        } catch (\Throwable $e) {
            // TODO - log $e, including message and stack trace
            return $this->failureJsonResponse('An error occurred', '', [], 500);
        }
    }

    protected function failureJsonResponse(
        string $errorMessage,
        string $errorType = '',
        $data = [],
        $httpStatus = 400
    ): JsonResponse {
        return Response::json([
            'success' => false,
            'error'   => [
                'message' => $errorMessage,
                'type'    => $errorType,
                'data'    => $data,
            ]
        ], $httpStatus);
    }

    protected function jsonResponse(array $data = [], int $httpStatus = 200): JsonResponse
    {
        return Response::json(
            array_merge([ 'success' => true ], $data),
            $httpStatus
        );
    }
}

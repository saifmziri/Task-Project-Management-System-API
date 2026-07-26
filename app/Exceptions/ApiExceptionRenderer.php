<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\ProjectHasTasksException;
use Throwable;

class ApiExceptionRenderer
{
    public static function render(Throwable $e)
    {
        /*
        |--------------------------------------------------------------------------
        | Model Not Found
        |--------------------------------------------------------------------------
        */

        if ($e instanceof ModelNotFoundException) {
            return self::modelNotFoundResponse($e);
        }

        /*
        |--------------------------------------------------------------------------
        | Route Not Found OR Wrapped ModelNotFoundException
        |--------------------------------------------------------------------------
        */

        if ($e instanceof NotFoundHttpException) {

            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                return self::modelNotFoundResponse($previous);
            }

            return ApiResponse::error(
                'The requested endpoint was not found.',
                null,
                Response::HTTP_NOT_FOUND,
                'ROUTE_NOT_FOUND'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Other Exceptions
        |--------------------------------------------------------------------------
        */

        return match (true) {

            $e instanceof ValidationException =>
                ApiResponse::error(
                    'Validation failed.',
                    $e->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'VALIDATION_ERROR'
                ),

            $e instanceof ProjectHasTasksException =>
                ApiResponse::error(
                $e->getMessage(),
                null,
                Response::HTTP_CONFLICT,
                'PROJECT_HAS_TASKS'
    ),

            $e instanceof AuthenticationException =>
                ApiResponse::error(
                    'Unauthenticated.',
                    null,
                    Response::HTTP_UNAUTHORIZED,
                    'UNAUTHENTICATED'
                ),

            $e instanceof ThrottleRequestsException =>
                ApiResponse::error(
                    'Too many requests.',
                    null,
                    Response::HTTP_TOO_MANY_REQUESTS,
                    'RATE_LIMITED'
                ),

            default =>
                ApiResponse::error(
                    app()->environment('production')
                        ? 'Server error.'
                        : $e->getMessage(),

                    app()->environment('production')
                        ? null
                        : ['exception' => class_basename($e)],

                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    'SERVER_ERROR'
                ),
        };
    }

    /**
     * Build a Model Not Found response.
     */
    private static function modelNotFoundResponse(ModelNotFoundException $e)
    {
        $model = class_basename($e->getModel());

        return ApiResponse::error(
            "{$model} not found.",
            null,
            Response::HTTP_NOT_FOUND,
            'RESOURCE_NOT_FOUND'
        );
    }
}
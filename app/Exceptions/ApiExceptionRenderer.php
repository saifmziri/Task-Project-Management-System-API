<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public static function render(Throwable $e)
    {
        // 1. التقاط الـ NotFoundHttpException أو ModelNotFoundException
        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            
            // محاولة استخراج اسم الـ Model من الاستثناء الداخلي
            $previous = $e->getPrevious();
            $modelName = 'Resource';

            if ($e instanceof ModelNotFoundException) {
                $modelName = class_basename($e->getModel());
            } elseif ($previous instanceof ModelNotFoundException) {
                $modelName = class_basename($previous->getModel());
            }

            return ApiResponse::error(
                "No query results for model [App\\Models\\{$modelName}]",
                ['exception' => 'NotFoundHttpException'],
                Response::HTTP_NOT_FOUND,
                'NOT_FOUND'
            );
        }

        // 2. باقي الاستثناءات
        return match (true) {
            $e instanceof ValidationException => ApiResponse::error('Validation failed.', $e->errors(), Response::HTTP_UNPROCESSABLE_ENTITY, 'VALIDATION_ERROR'),
            $e instanceof AuthenticationException => ApiResponse::error('Unauthenticated.', null, Response::HTTP_UNAUTHORIZED, 'UNAUTHENTICATED'),
            $e instanceof ThrottleRequestsException => ApiResponse::error('Too many requests.', null, Response::HTTP_TOO_MANY_REQUESTS, 'RATE_LIMITED'),
            default => ApiResponse::error(
                app()->environment('production') ? 'Server error.' : $e->getMessage(),
                app()->environment('production') ? null : ['exception' => class_basename($e)],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'SERVER_ERROR'
            ),
        };
    }
}
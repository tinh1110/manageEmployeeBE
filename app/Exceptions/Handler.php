<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     * @throws \ReflectionException
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            if ($exception instanceof PostTooLargeException) {
                $message = "Size of attached file should be less " . ini_get("upload_max_filesize") . "B";
                return $this->sendError($message);
            }

            if ($exception instanceof AuthenticationException) {
                $message = 'Unauthenticated or token expired, please login';
                return $this->sendError($message, null, Response::HTTP_UNAUTHORIZED);
            }

            if ($exception instanceof ThrottleRequestsException) {
                $message = 'Too many requests, please slow down';
                return $this->sendError($message, null, Response::HTTP_TOO_MANY_REQUESTS);
            }

            if ($exception instanceof ModelNotFoundException) {
                $message = 'Entry for ' . str_replace('App\\', '', $exception->getModel()) . ' not found';
                return $this->sendError($message, null, Response::HTTP_NOT_FOUND);
            }

            if ($exception instanceof QueryException) {
                $message = 'There was issue with the query' . ', errors:' . $exception;
                return $this->sendError($message);

            }

            if ($exception instanceof \Error) {
                $message = "There was some internal error" . ', errors:' . $exception->getMessage();
                return $this->sendError($message);
            }

            $statusCode = 500;
            if (method_exists($exception, 'getStatusCode')) {
                $statusCode = $exception->getStatusCode();
            }

            return response()->json(
                [
                    'status' => false,
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode()
                ],
                $statusCode
            );
        }

        if ($exception instanceof Responsable) {
            return $exception->toResponse($request);
        }

        $exception = $this->prepareException($this->mapException($exception));

        if ($response = $this->renderViaCallbacks($request, $exception)) {
            return $response;
        }

        return match (true) {
            $exception instanceof HttpResponseException => $exception->getResponse(),
            $exception instanceof AuthenticationException => $this->unauthenticated($request, $exception),
            $exception instanceof ValidationException => $this->convertValidationExceptionToResponse($exception, $request),
            default => $this->renderExceptionResponse($request, $exception),
        };
    }
}

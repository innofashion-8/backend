<?php

use App\Utils\HttpResponseCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                
                $statusCode = HttpResponseCode::HTTP_INTERNAL_SERVER_ERROR;
                $message = app()->isProduction() 
                        ? 'Terjadi kesalahan pada server (Internal Server Error).' 
                        : $e->getMessage();
                $data = null;

                if ($e instanceof ValidationException) {
                    $statusCode = HttpResponseCode::HTTP_UNPROCESSABLE_ENTITY;
                    $message = $e->validator->errors()->first(); 
                    $data = $e->errors(); 
                } 
                elseif ($e instanceof AuthenticationException) {
                    $statusCode = HttpResponseCode::HTTP_UNAUTHORIZED;
                    $message = 'Unauthenticated access.';
                } 
                elseif ($e instanceof ModelNotFoundException) {
                    $statusCode = HttpResponseCode::HTTP_NOT_FOUND;
                    
                    $modelName = class_basename($e->getModel()); 
                    
                    $message = "Data {$modelName} tidak ditemukan."; 
                }
                elseif ($e instanceof NotFoundHttpException) {
                    $statusCode = HttpResponseCode::HTTP_NOT_FOUND;
                    $message = 'Resource not found.';
                } 
                elseif ($e instanceof HttpException) {
                    $statusCode = $e->getStatusCode();
                }

                return response()->json([
                    'code'    => $statusCode,
                    'success' => false,
                    'message' => $message,
                    'data'    => $data,
                ], $statusCode);
            }
        });
    })->create();

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\ModelNotFoundException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Auth\AuthenticationException;


 
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 404,
                'data'=>[],
				'message' => 'The specified URL cannot be found',
				
			],404,[],JSON_FORCE_OBJECT);

        });

        $this->renderable(function (TokenExpiredException $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 404,
                'data'=>[],
				'message' => 'Token expired',
				
			],404,[],JSON_FORCE_OBJECT);

        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 405,
                'data'=>[],
				'message' => 'The specified method for the request is invalid',
				
			],405,[],JSON_FORCE_OBJECT);

        });

        $this->renderable(function (HttpException $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 500,
                'data'=>[],
				'message' => $e->getMessage(),
				
			],500,[],JSON_FORCE_OBJECT);

        });

        $this->renderable(function (ModelNotFoundException  $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 500,
                'data'=>[],
				'message' => 'Data Not Found',
				
			],500,[],JSON_FORCE_OBJECT);

        });

        $this->renderable(function (TokenMismatchException   $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 500,
                'data'=>[],
				'message' => 'Token Mismatch',
				
			],500,[],JSON_FORCE_OBJECT);

        });

        $this->renderable(function (AuthenticationException   $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 500,
                'data'=>[],
				'message' => 'Authentication failed',
				
			],500,[],JSON_FORCE_OBJECT);

        });


        $this->renderable(function (Exception   $e, $request) {
            return response()->json([
				'status' => false,
                'code' => 500,
                'data'=>[],
                'message' => $e->getMessage(),
				
			],500,[],JSON_FORCE_OBJECT);

        });


      
    }
    
}

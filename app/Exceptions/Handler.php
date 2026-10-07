<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Database terputus / query gagal (mis. koneksi DB down, maintenance)
        if ($exception instanceof PDOException || $exception instanceof QueryException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Server maintenance',
                ], 503);
            }
            return response()->view('errors.db_maintenance', [], 503);
        }

        // Session/CSRF token expired saat login
        if ($exception instanceof TokenMismatchException) {
            $message = 'Session login timeout, silahkan reload halaman web.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->with('error', $message);
        }

        return parent::render($request, $exception);
    }
}
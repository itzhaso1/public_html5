<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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

        $this->renderable(function (PostTooLargeException $e, Request $request) {
            $path = trim((string) $request->path(), '/');
            $isPublish = str_contains($path, 'publish-product') || preg_match('~(^|/)product$~', $path);

            $message = $isPublish
                ? 'حجم صور الحساب كبير جدًا. قلّل دقة الصور أو ارفع عددًا أقل في كل محاولة.'
                : 'حجم الملف كبير. رجاءً ارفع إيصال أصغر أو صورة بدقة أقل.';

            $errorKey = $isPublish ? 'gallery' : 'receipt';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => [$errorKey => [$message]],
                ], 413);
            }

            $redirect = back()->withErrors([$errorKey => $message])->withInput();
            if ($isPublish) {
                $redirect->with('wizard_force_step', 6);
            }

            return $redirect;
        });

        $this->renderable(function (TokenMismatchException $e, Request $request) {
            $message = 'انتهت صلاحية الجلسة. رجاءً حدّث الصفحة ثم حاول مرة أخرى.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'error' => 'session_expired',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except('_token'))
                ->with('error', $message);
        });
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AdminUserMessageNotification;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use App\Support\WhatsApp\WasenderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class UserMessageController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'phone'])
            ->latest('id')
            ->limit(500)
            ->get();

        // Keep count logic identical to broadcast targets so UI number is accurate.
        $publishersCount = $this->publisherTargets()->count();

        return view('dashboard.admin.user_messages.index', [
            'pageTitle' => 'الرسائل للمستخدمين',
            'users' => $users,
            'publishersCount' => $publishersCount,
        ]);
    }

    public function broadcastToPublishers(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:4000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'string', 'in:site,email,whatsapp'],
        ]);

        $channels = array_values(array_unique((array) ($data['channels'] ?? [])));
        $targets = $this->publisherTargets();
        if ($targets->isEmpty()) {
            return back()->withErrors(['error' => 'لا توجد حسابات منشورة من صفحة النشر لإرسال الرسالة لها.']);
        }

        $adminId = (int) (auth('admin')->id() ?: 0);
        $siteSent = 0;
        $emailSent = 0;
        $waSent = 0;

        foreach ($targets as $target) {
            $targetEmail = trim((string) ($target->client_email ?? ''));
            if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                $targetEmail = '';
            }

            $targetPhone = WhatsAppNumber::normalize((string) ($target->client_number ?? ''));
            $personalMessage = $this->appendAccountContext((string) $data['message'], $target);

            if (in_array('site', $channels, true)) {
                $users = $this->usersMatchingContacts(
                    $targetEmail !== '' ? [$targetEmail] : [],
                    $targetPhone !== '' ? [$targetPhone] : []
                );
                if ($users->isNotEmpty()) {
                    $notification = new AdminUserMessageNotification(
                        (string) $data['title'],
                        $personalMessage,
                        $adminId
                    );
                    foreach ($users as $u) {
                        try {
                            $u->notify($notification);
                            $siteSent++;
                        } catch (\Throwable $e) {
                            report($e);
                        }
                    }
                }
            }

            if (in_array('email', $channels, true) && $targetEmail !== '') {
                try {
                    EmailNotifier::sendAfterCommit($targetEmail, (string) $data['title'], $personalMessage);
                    $emailSent++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            if (in_array('whatsapp', $channels, true) && $targetPhone !== '') {
                try {
                    WasenderNotifier::sendAfterCommit($targetPhone, $personalMessage);
                    $waSent++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return back()->with('success', 'تم إرسال الرسالة بنجاح — إعلانات: '
            . $targets->count()
            . ' | داخل الموقع: '
            . $siteSent
            . ' | بريد: '
            . $emailSent
            . ' | واتساب: '
            . $waSent);
    }

    public function sendToUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:4000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'string', 'in:site,email,whatsapp'],
        ]);

        $user = User::query()->findOrFail((int) $data['user_id']);
        $channels = array_values(array_unique((array) ($data['channels'] ?? [])));

        $siteSent = 0;
        if (in_array('site', $channels, true)) {
            try {
                $user->notify(new AdminUserMessageNotification(
                    (string) $data['title'],
                    (string) $data['message'],
                    (int) (auth('admin')->id() ?: 0)
                ));
                $siteSent = 1;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $emailSent = 0;
        if (in_array('email', $channels, true) && filter_var((string) $user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                EmailNotifier::sendAfterCommit((string) $user->email, (string) $data['title'], (string) $data['message']);
                $emailSent = 1;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $waSent = 0;
        if (in_array('whatsapp', $channels, true)) {
            $normalized = WhatsAppNumber::normalize((string) $user->phone);
            if ($normalized !== '') {
                try {
                    $waSent = WasenderNotifier::send($normalized, (string) $data['message']) ? 1 : 0;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return back()->with('success', 'تم إرسال الرسالة للمستخدم #' . $user->id
            . ' — داخل الموقع: '
            . $siteSent
            . ' | بريد: '
            . $emailSent
            . ' | واتساب: '
            . $waSent);
    }

    /**
     * @param array<int,string> $emails
     * @param array<int,string> $phones
     */
    private function usersMatchingContacts(array $emails, array $phones): Collection
    {
        $emails = array_values(array_unique(array_filter(array_map('strtolower', $emails))));
        $emailSet = array_fill_keys($emails, true);
        $phoneSet = array_fill_keys($phones, true);

        $query = User::query()->select(['id', 'email', 'phone']);
        if (!empty($emails) && !empty($phoneSet)) {
            $query->where(function ($q) use ($emails) {
                $q->whereIn('email', $emails)
                    ->orWhereNotNull('phone');
            });
        } elseif (!empty($emails)) {
            $query->whereIn('email', $emails);
        } elseif (!empty($phoneSet)) {
            $query->whereNotNull('phone');
        } else {
            return collect();
        }

        $users = $query->get();
        return $users->filter(function (User $user) use ($phoneSet, $emailSet) {
            $email = strtolower(trim((string) $user->email));
            $p = WhatsAppNumber::normalize((string) $user->phone);
            return isset($emailSet[$email]) || ($p !== '' && isset($phoneSet[$p]));
        })->values();
    }

    /**
     * @return Collection<int,Product>
     */
    private function publisherTargets(): Collection
    {
        try {
            $hasClientEmail = Schema::hasColumn('products', 'client_email');
            $hasClientNumber = Schema::hasColumn('products', 'client_number');
            if (!$hasClientEmail && !$hasClientNumber) {
                return collect();
            }

            // IMPORTANT: do not select "name" from products table (translatable field).
            // Selecting a non-existing column here causes SQL error and returns empty targets.
            $select = ['id', 'slug', 'status'];
            $hasPublishSource = Schema::hasColumn('products', 'publish_source');
            if ($hasPublishSource) {
                $select[] = 'publish_source';
            }
            if ($hasClientEmail) {
                $select[] = 'client_email';
            }
            if ($hasClientNumber) {
                $select[] = 'client_number';
            }

            $q = Product::query()
                ->select($select)
                ->with(['translations']);

            // Only keep rows with at least one contact method.
            $q->where(function ($c) use ($hasClientEmail, $hasClientNumber) {
                if ($hasClientEmail && $hasClientNumber) {
                    $c->where(function ($e) {
                        $e->whereNotNull('client_email')->where('client_email', '!=', '');
                    })->orWhere(function ($p) {
                        $p->whereNotNull('client_number')->where('client_number', '!=', '');
                    });
                } elseif ($hasClientEmail) {
                    $c->where(function ($e) {
                        $e->whereNotNull('client_email')->where('client_email', '!=', '');
                    });
                } else {
                    $c->where(function ($p) {
                        $p->whereNotNull('client_number')->where('client_number', '!=', '');
                    });
                }
            });

            if ($hasPublishSource) {
                $q->where(function ($w) use ($hasClientEmail, $hasClientNumber) {
                    // Primary target: explicitly public-published rows.
                    $w->where('publish_source', 'public');

                    // Legacy fallback: rows created before publish_source existed.
                    $w->orWhere(function ($legacy) use ($hasClientEmail, $hasClientNumber) {
                        $legacy->whereNull('publish_source');
                        $legacy->where(function ($c) use ($hasClientEmail, $hasClientNumber) {
                            if ($hasClientEmail && $hasClientNumber) {
                                $c->where(function ($e) {
                                    $e->whereNotNull('client_email')->where('client_email', '!=', '');
                                })->orWhere(function ($p) {
                                    $p->whereNotNull('client_number')->where('client_number', '!=', '');
                                });
                            } elseif ($hasClientEmail) {
                                $c->where(function ($e) {
                                    $e->whereNotNull('client_email')->where('client_email', '!=', '');
                                });
                            } else {
                                $c->where(function ($p) {
                                    $p->whereNotNull('client_number')->where('client_number', '!=', '');
                                });
                            }
                        });
                    });
                });
            }

            return $q->latest('id')->get();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    private function appendAccountContext(string $baseMessage, Product $product): string
    {
        $base = trim($baseMessage);
        $url = $this->accountUrlForMessage($product);
        $name = trim((string) ($product->name ?? ''));
        if ($name === '') {
            try {
                $name = trim((string) ($product->translate('ar')->name ?? ''));
            } catch (\Throwable $e) {
                $name = '';
            }
        }

        $details = [];
        if ($name !== '') {
            $details[] = 'اسم الحساب: ' . $name;
        }
        $details[] = 'رقم الإعلان: #' . (int) $product->id;
        if ($url !== '') {
            $details[] = 'رابط الإعلان: ' . $url;
        }

        $suffix = implode("\n", $details);
        if ($suffix === '') {
            return $base;
        }

        return $base . "\n\n" . $suffix;
    }

    private function accountUrlForMessage(Product $product): string
    {
        try {
            if ((string) ($product->status ?? '') === 'published') {
                return route('website.product.show', ['product' => $product->id]);
            }
            if (!empty($product->slug)) {
                return route('public.products.track', ['slug' => $product->slug]);
            }
            return route('website.product.show', ['product' => $product->id]);
        } catch (\Throwable $e) {
            return '';
        }
    }
}


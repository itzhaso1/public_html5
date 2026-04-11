<?php

namespace App\Repositories;

use App\Dto\UserDto;
use App\Models\User;
use App\Services\Contracts\AdminUserInterface;
use App\DataTables\Dashboard\Admin\UserDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUserRepository implements AdminUserInterface
{
    protected $model;

    public function __construct()
    {
        $this->model = new User;
    }

    public function index(UserDataTable $userDataTable)
    {
        return $userDataTable->render('dashboard.admin.users.index', ['pageTitle' => 'المستخدمين']);
    }

    public function edit(User $user)
    {
        $user->loadMissing(['profile']);

        return view('dashboard.admin.users.edit', [
            'pageTitle' => 'تعديل المستخدم',
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:64'],
            'profile_phone' => ['nullable', 'string', 'max:64'],
            'wallet_points_balance' => ['nullable', 'integer', 'min:0', 'max:1000000000000'],
            'is_merchant' => ['nullable', 'boolean'],
        ]);

        $phone = isset($data['phone']) ? trim((string) $data['phone']) : null;
        if ($phone === '') $phone = null;
        $profilePhone = isset($data['profile_phone']) ? trim((string) $data['profile_phone']) : null;
        if ($profilePhone === '') $profilePhone = null;

        DB::transaction(function () use ($user, $data, $phone, $profilePhone) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $phone,
                'wallet_points_balance' => $data['wallet_points_balance'] ?? $user->wallet_points_balance,
                'is_merchant' => (bool) ($data['is_merchant'] ?? $user->is_merchant),
            ]);

            // Keep profile phone in sync (some flows read it as fallback)
            $user->loadMissing(['profile']);
            if ($user->profile) {
                $user->profile->update(['phone' => $profilePhone]);
            } elseif ($profilePhone !== null) {
                $user->profile()->create([
                    'phone' => $profilePhone,
                ]);
            }
        });

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'تم تحديث بيانات المستخدم بنجاح ✅');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'تم الحذف بنجاح!');
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\Facades\AdminFacade;
use App\Services\Contracts\AdminUserInterface;
use App\DataTables\Dashboard\Admin\UserDataTable;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserDataTable $userDataTable, protected AdminUserInterface $adminUserInterface)
    {
        $this->adminUserInterface = $adminUserInterface;
        $this->userDataTable = $userDataTable;
    }

    public function index(UserDataTable $userDataTable)
    {
        return $this->adminUserInterface->index($this->userDataTable);
    }

    public function edit(User $user)
    {
        return $this->adminUserInterface->edit($user);
    }

    public function update(Request $request, User $user)
    {
        return $this->adminUserInterface->update($request, $user);
    }

    public function destroy(User $user)
    {
        return $this->adminUserInterface->destroy($user);
    }
}

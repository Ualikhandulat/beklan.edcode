<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('group')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('login', 'like', "%{$request->search}%")
                        ->orWhere('iin', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate()
            ->withQueryString();

        $navigations = [
            route('admin.users.index') => 'Пользователи',
        ];

        return view('admin.users.index', compact('users', 'navigations'));
    }

    /** Archive: list soft-deleted users with the option to restore them. */
    public function archive(Request $request): View
    {
        $users = User::onlyTrashed()
            ->with('group')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('login', 'like', "%{$request->search}%")
                        ->orWhere('iin', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('deleted_at')
            ->paginate()
            ->withQueryString();

        $navigations = [
            route('admin.users.index') => 'Пользователи',
            '#' => 'Архив',
        ];

        return view('admin.users.archive', compact('users', 'navigations'));
    }

    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        return redirect()->route('admin.users.archive')
            ->with('success', "{$user->name} восстановлен.");
    }

    public function create(): View
    {
        $navigations = [
            route('admin.users.index') => 'Пользователи',
            '#' => 'Добавить пользователя',
        ];

        return view('admin.users.form', [
            'user' => new User,
            'groups' => Group::orderBy('title')->pluck('title', 'id'),
            'roles' => collect(Role::cases())->mapWithKeys(fn ($r) => [$r->value => $r->title()]),
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'navigations' => $navigations,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь создан.');
    }

    public function edit(User $user): View
    {
        $navigations = [
            route('admin.users.index') => 'Пользователи',
            '#' => $user->name,
        ];

        return view('admin.users.form', [
            'user' => $user,
            'groups' => Group::orderBy('title')->pluck('title', 'id'),
            'roles' => collect(Role::cases())->mapWithKeys(fn ($r) => [$r->value => $r->title()]),
            'action' => route('admin.users.update', $user),
            'method' => 'PUT',
            'navigations' => $navigations,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь обновлён.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь удалён.');
    }
}

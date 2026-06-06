<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\Admin\UpdateGroupRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $groups = Group::withCount('users')
            ->when($request->search, fn ($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $navigations = [
            route('admin.groups.index') => 'Группы',
        ];

        return view('admin.groups.index', compact('groups', 'navigations'));
    }

    public function show(Group $group): View
    {
        $groups = Group::withCount('users')->latest()->get();
        $students = $group->users()->orderBy('name')->get();
        $available = User::where('role', Role::Student)
            ->where(fn ($q) => $q->whereNull('group_id')->orWhere('group_id', '!=', $group->id))
            ->orderBy('name')
            ->get();

        $navigations = [
            route('admin.groups.index') => 'Группы',
            '#' => $group->title,
        ];

        return view('admin.groups.show', compact('group', 'groups', 'students', 'available', 'navigations'));
    }

    public function addStudent(Request $request, Group $group): RedirectResponse
    {
        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $user = User::findOrFail($request->user_id);
        $user->update(['group_id' => $group->id]);

        return redirect()->route('admin.groups.show', $group)
            ->with('success', "{$user->name} добавлен в группу.");
    }

    public function removeStudent(Group $group, User $user): RedirectResponse
    {
        if ($user->group_id === $group->id) {
            $user->update(['group_id' => null]);
        }

        return redirect()->route('admin.groups.show', $group)
            ->with('success', "{$user->name} удалён из группы.");
    }

    public function create(): View
    {
        $navigations = [
            route('admin.groups.index') => 'Группы',
            '#' => 'Добавить группу',
        ];

        return view('admin.groups.form', [
            'group' => new Group,
            'action' => route('admin.groups.store'),
            'method' => 'POST',
            'navigations' => $navigations,
        ]);
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        Group::create($request->validated());

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа создана.');
    }

    public function edit(Group $group): View
    {
        $navigations = [
            route('admin.groups.index') => 'Группы',
            '#' => $group->title,
        ];

        return view('admin.groups.form', [
            'group' => $group,
            'action' => route('admin.groups.update', $group),
            'method' => 'PUT',
            'navigations' => $navigations,
        ]);
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $group->update($request->validated());

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа обновлена.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа удалена.');
    }
}

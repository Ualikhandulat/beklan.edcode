<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\Admin\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $groups = Group::withCount('users')
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $navigations = [
            route('admin.groups.index') => 'Группы',
        ];

        return view('admin.groups.index', compact('groups', 'navigations'));
    }

    public function create(): View
    {
        $navigations = [
            route('admin.groups.index') => 'Группы',
            '#'                         => 'Добавить группу',
        ];

        return view('admin.groups.form', [
            'group'       => new Group(),
            'action'      => route('admin.groups.store'),
            'method'      => 'POST',
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
            '#'                         => $group->title,
        ];

        return view('admin.groups.form', [
            'group'       => $group,
            'action'      => route('admin.groups.update', $group),
            'method'      => 'PUT',
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

@props(['route'])

<form method="POST" action="{{ $route }}"
      onsubmit="return confirm('Восстановить пользователя?')">
    @csrf
    @method('PATCH')
    <button {{ $attributes->class(['btn btn-ghost btn-sm text-text-muted hover:text-success hover:bg-success-light']) }}>
        <x-icon name="refresh" class="w-4 h-4" />
    </button>
</form>

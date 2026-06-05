@props(['route'])

<form method="POST" action="{{ $route }}"
      onsubmit="return confirm('Вы действительно хотите удалить?')">
    @csrf
    @method('DELETE')
    <button {{ $attributes->class(['btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light']) }}>
        <x-icon name="trash" class="w-4 h-4" />
    </button>
</form>

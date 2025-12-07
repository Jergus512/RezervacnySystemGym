@extends('layouts.app')

@section('title', 'Správa používateľov')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Používatelia</h4>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ Nový používateľ</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
        <tr>
            <th>ID</th>
            <th>Meno</th>
            <th>Email</th>
            <th>Admin</th>
            <th class="text-end">Akcie</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->is_admin)
                        <span class="badge bg-success">Admin</span>
                    @else
                        <span class="badge bg-secondary">Bežný</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch justify-content-end">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary w-100 w-sm-auto">Upraviť</a>

                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="m-0 w-100 w-sm-auto"
                              onsubmit="return confirm('Naozaj chceš zmazať tohto používateľa?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 w-sm-auto">Zmazať</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Žiadni používatelia.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $users->links() }}
</div>
@endsection

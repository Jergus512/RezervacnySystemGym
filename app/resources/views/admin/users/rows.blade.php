@foreach ($users as $user)
    <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
            @if($user->is_admin)
                <span class="badge bg-success">Admin</span>
            @elseif($user->is_trainer)
                <span class="badge bg-info text-dark">Tréner</span>
            @elseif($user->is_reception)
                <span class="badge bg-warning text-dark">Recepcia</span>
            @else
                <span class="badge bg-secondary">Bežný</span>
            @endif
        </td>
        <td>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Upraviť</a>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Naozaj chcete odstrániť tohto používateľa?')">Odstrániť</button>
            </form>
        </td>
    </tr>
@endforeach

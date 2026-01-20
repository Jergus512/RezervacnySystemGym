@extends('layouts.app')

@section('title', 'Recepcia')

@section('content')
    <div class="alert alert-warning">
        Recepčný dashboard bol odstránený. Použi prosím navigáciu hore alebo prejdi na
        <a href="{{ route('reception.calendar') }}">kalendár tréningov</a>.
    </div>
@endsection

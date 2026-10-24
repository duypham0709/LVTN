@extends('layouts.admin')

@section('title', '403')

@section('content')
    <div class="right_col text-center" role="main">
        <h1>403</h1>
        <p>{{ $message }}</p>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary"
            onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">
            Quay lại
        </a>
    </div>
@endsection

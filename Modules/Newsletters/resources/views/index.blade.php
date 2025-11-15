@extends('layouts.admin')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {{ config('newsletters.name') }}</p>
@endsection

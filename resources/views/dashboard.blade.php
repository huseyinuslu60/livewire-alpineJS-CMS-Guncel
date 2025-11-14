@extends('layouts.admin')

@section('title', 'Dashboard - ' . auth()->user()->roles->first()->display_name ?? 'Panel')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('styles')
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endsection

@section('content')

<!-- Dinamik Dashboard - Kullanıcının yetkilerine göre içerik gösterir -->
@include('dashboard.dynamic')

@endsection
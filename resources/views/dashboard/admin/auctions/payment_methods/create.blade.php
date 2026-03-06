@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<form method="POST" action="{{ route('admin.auctions.payment_methods.store') }}">
    @csrf
    @include('dashboard.admin.auctions.payment_methods._form')
</form>
@endsection

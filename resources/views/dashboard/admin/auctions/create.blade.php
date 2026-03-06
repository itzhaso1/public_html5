@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<form method="POST" action="{{ route('admin.auctions.store') }}" enctype="multipart/form-data">
    @csrf
    @include('dashboard.admin.auctions._form')
</form>
@endsection

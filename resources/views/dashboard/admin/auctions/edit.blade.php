@extends('dashboard.layouts.master')

@section('pageTitle')
{{ $PageTitle }}
@endsection

@section('content')
<form method="POST" action="{{ route('admin.auctions.update', $auction) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('dashboard.admin.auctions._form')
</form>
@endsection

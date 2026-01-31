@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @livewire('dashboard')
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'خوش‌آمدگویی')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @livewire('admin.welcome')
        </div>
    </div>
</div>
@endsection

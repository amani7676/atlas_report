@extends('layouts.app')

@section('title', 'پیام‌های خوش‌آمدگویی')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">پیام‌های خوش‌آمدگویی</h3>
                </div>
                <div class="card-body">
                    @livewire('welcome-messages.index')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

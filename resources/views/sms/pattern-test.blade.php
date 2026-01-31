@extends('layouts.app')

@section('title', 'تست پیامک الگو')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تست پیامک الگو</h3>
                </div>
                <div class="card-body">
                    @livewire('sms.pattern-test')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'پیامک تخلف')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">پیامک تخلف</h3>
                </div>
                <div class="card-body">
                    @livewire('sms.violation')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

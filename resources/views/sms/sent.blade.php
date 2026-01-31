@extends('layouts.app')

@section('title', 'پیامک‌های ارسال شده')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">پیامک‌های ارسال شده</h3>
                </div>
                <div class="card-body">
                    @livewire('sms.sent-messages')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

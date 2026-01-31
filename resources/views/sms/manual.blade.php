@extends('layouts.app')

@section('title', 'پیامک دستی')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">پیامک دستی</h3>
                </div>
                <div class="card-body">
                    @livewire('sms.manual')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

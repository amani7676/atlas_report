@extends('layouts.app')

@section('title', 'گزارش‌های اقامت‌گران')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">گزارش‌های اقامت‌گران</h3>
                </div>
                <div class="card-body">
                    @livewire('residents.resident-reports')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

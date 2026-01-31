@extends('layouts.app')

@section('title', 'الگوها')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">الگوها</h3>
                </div>
                <div class="card-body">
                    @livewire('patterns.index')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

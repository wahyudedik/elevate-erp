@extends('layout')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Thank You</div>
                <div class="card-body">
                    <h2>{{ $message }}</h2>
                    <p></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

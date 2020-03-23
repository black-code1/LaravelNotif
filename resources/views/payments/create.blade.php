@extends('layouts.app')

@section('content')
    <div class="container">
      <form method="POST" action="/payments">
        @csrf

        <button type="submit" class="btn btn-primary">
          Make Payment
        </button>
      </form>
    </div>
@endsection
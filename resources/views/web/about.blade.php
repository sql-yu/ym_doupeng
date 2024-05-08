@extends('layouts.app')

@section('title',$title)
@section('keywords',$keywords)
@section('description',$description)

@section('content')
    <div class="footer-container @yield('check_code')">{!! $des !!}</div>
@endsection

@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE



    {{--Customers Logo--}}
    @include('front.components.customer_logo', ['customers' => $customers])


@endsection

@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')


    //INCLUDE YOUR CODE HERE

   @foreach($policies as $policy)
       <h4>{{ $policy->title }}</h4>
       <p>{{ $policy->text }}</p>
   @endforeach


@endsection

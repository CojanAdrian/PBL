@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE

    @foreach($terms as $term)

        <h4>{{ $term->title }}</h4>
        <p>{{ $term->text }}</p>

    @endforeach


@endsection

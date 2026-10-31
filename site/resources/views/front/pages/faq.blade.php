@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE

    @foreach($faqs as $faq)
        <h4>{{ $faq->title }}</h4>
        <p>{{ $faq->text }}</p>
    @endforeach


@endsection

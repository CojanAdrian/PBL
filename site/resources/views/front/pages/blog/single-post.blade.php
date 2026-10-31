@extends('layouts.front')
@section('title', $postMeta->title)
@section('content')

    //INCLUDE YOUR CODE HERE


@endsection


{{--<small>--}}
{{--    {{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}--}}
{{--</small>--}}

{{--<h1>{{ $post->title }}</h1>--}}
{{--<h3>{{ $post->sub_title }}</h3>--}}

{{--<img src="{{ storage('post', $post->img) }}" alt="">--}}

{{--<p>{!! $post->text !!}</p>--}}



{{--    Relevant Posts--}}

{{--<br><br><br>--}}

{{--<h1>Relevant Posts</h1>--}}

{{--@foreach($relevantPosts as $relevantPost)--}}

{{--    <a href="{{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}">--}}

{{--        <img src="{{ storage('post', $relevantPost->img) }}" alt="">--}}

{{--        <h5>{{ $relevantPost->title }}</h5>--}}

{{--        <h6>{{ $relevantPost->sub_title }}</h6>--}}

{{--    </a>--}}

{{--@endforeach--}}

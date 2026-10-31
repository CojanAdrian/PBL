@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE


    {{--Blog--}}
    <h1>-Blog-</h1>
    @include('front.components.posts_home_page', ['posts' => $posts])



@endsection


{{--@forelse($posts as $post)--}}

{{--    img - {{ asset('storage/post/'. $post->img ) }}--}}

{{--    link - {{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}--}}

{{--    title - {{ $post->title }}--}}

{{--    excerpt - {{ $post->excerpt }}--}}

{{--    subtitle - {{ $post->sub_title }}--}}

{{--    text - {!! $post->text !!}--}}

{{--@empty--}}
{{--    <h4>No posts added</h4>--}}
{{--@endforelse--}}

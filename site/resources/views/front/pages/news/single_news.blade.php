@extends('layouts.front')
@section('title', $newsMeta?->title ?? $news->title)
@section('content')

    //INCLUDE YOUR CODE HERE



@endsection



{{--<small>--}}
{{--    {{ \Carbon\Carbon::parse($news->created_at)->format('d-m-Y') }}--}}
{{--</small>--}}
{{--<h2>{!!  $news->title !!}</h2>--}}
{{--<h5>--}}
{{--    {!!  $news->sub_title  !!}--}}
{{--</h5>--}}

{{--img - <img src="{{ asset('storage/news/'. $news->img ) }}" alt="">--}}

{{--cover img - <img src="{{ asset('storage/news/'. $news->cover_img ) }}" alt="">--}}

{{--<p>--}}
{{--    {!! $news->text !!}--}}
{{--</p>--}}

{{--@foreach($news->gallery as $img)--}}
{{--    <div class="col-md-4 position-relative" style="padding-top: 2%">--}}
{{--        <img src="{{ asset('storage/news/'. $img->img) }}" alt="gallery img" class="img-fluid">--}}
{{--    </div>--}}
{{--@endforeach--}}




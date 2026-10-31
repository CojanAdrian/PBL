@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE


    {{--All Services--}}
    <h1>-All Services-</h1>
    @include('front.components.services', ['services' => $services])



@endsection


{{--@forelse($services as $service)--}}
{{--    --}}
{{--    img - {{ asset('storage/service/'. $service->img ) }}--}}
{{--    --}}
{{--    link - {{ route('front.single.service', ['locale'=>app()->getLocale(), 'slug'=>$service->slug]) }}--}}
{{--    --}}
{{--    title - {{ $service->title }}--}}
{{--    --}}
{{--    subtitle - {{ $service->sub_title }}--}}
{{--    --}}
{{--    text - {{ $service->text }}--}}

{{--@empty--}}
{{--    <h1>No services Loaded</h1>--}}
{{--@endforelse--}}

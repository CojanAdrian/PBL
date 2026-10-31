@extends('layouts.front')
@section('title', $meta?->title ?? '')
@section('content')

    //INCLUDE YOUR CODE HERE


@endsection


{{--@forelse($projects as $project)--}}
{{--    --}}
{{--    img - {{ asset('storage/project/'. $project->img ) }}--}}
{{--    --}}
{{--    link - {{ route('front.single.project', ['locale'=>app()->getLocale(), 'slug'=>$project->slug]) }}--}}
{{--    --}}
{{--    title - {{ $project->title }}--}}
{{--    --}}
{{--    subtitle - {{ $project->sub_title }}--}}
{{--    --}}
{{--    text -  {{ $project->text }}--}}

{{--    {{ \Carbon\Carbon::parse($project->created_at)->format('d-m-Y') }}--}}

{{--@empty--}}
{{--    <h1>No Projects Loaded</h1>--}}
{{--@endforelse--}}

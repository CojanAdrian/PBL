@foreach($services as $service)

    img - <img src="{{ asset('storage/service/'. $service->img ) }}" alt="">

    link - <a href="{{ route('front.single.service', ['locale'=>app()->getLocale(), 'slug'=>$service->slug]) }}"></a>

    title - <h3> {{ $service->title }} </h3>

    subtitle - <h6> {{ $service->sub_title }} </h6>

    text - <p> {!!  $service->text  !!} </p>

@endforeach

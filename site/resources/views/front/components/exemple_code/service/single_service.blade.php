
<h1>
    {!!  $service->title  !!}
</h1>

<img src="{{ asset('storage/service/'. $service->img ) }}" alt="Image">

<h4>
    {!!  $service->sub_title  !!}
</h4>
<p>
    {!!  $service->text  !!}
</p>

<p>
    {{ \Carbon\Carbon::parse($service->created_at)->format('d-m-Y') }}
</p>

    <span>
        <a href="{{ route('front.single.service', ['locale'=>app()->getLocale(), 'slug'=>$service->slug]) }}">
            Link to single service
        </a>
    </span>

@foreach($service->gallery as $img)
    <div class="col-md-4">
        <img src="{{ asset('storage/service/'. $img->img) }}" alt="gallery img" class="img-fluid">
    </div>
@endforeach

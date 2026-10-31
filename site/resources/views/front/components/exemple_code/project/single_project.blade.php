
<h1>
    {{ $project->title }}
</h1>

<img src="{{ asset('storage/project/'. $project->img ) }}" alt="Image">

<h4>
    {{ $project->sub_title }}
</h4>
<p>
    {!!  $project->text  !!}
</p>
<p>
    {{ \Carbon\Carbon::parse($project->created_at)->format('d-m-Y') }}
</p>

@foreach($project->gallery as $img)
    <div class="col-md-4">
        <img src="{{ asset('storage/project/'. $img->image) }}" alt="gallery img" class="img-fluid">
    </div>
@endforeach

<span>
        <a href="{{ route('front.single.project', ['locale'=>app()->getLocale(), 'slug'=>$project->slug]) }}">
            Link to single project
        </a>
    </span>



@foreach($projects as $project)

    img - <img src="{{ asset('storage/project/'. $project->img ) }}" alt="">

    link - <a href="{{ route('front.single.project', ['locale'=>app()->getLocale(), 'slug'=>$project->slug]) }}"></a>

    title - <h3> {{ $project->title }} </h3>

    subtitle - <h6> {{ $project->sub_title }} </h6>

    text -  <p> {!! $project->text !!} </p>

    <small>{{ \Carbon\Carbon::parse($project->created_at)->format('d-m-Y') }}</small>

@endforeach

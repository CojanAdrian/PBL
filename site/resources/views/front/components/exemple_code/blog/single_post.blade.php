<small>
    {{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}
</small>

<h1>{{ $post->title }}</h1>

<h3>{{ $post->sub_title }}</h3>

<img src="{{ asset('storage/post/'. $post->img ) }}" alt="">

<p>{!! $post->text !!}</p>


@foreach($post->gallery as $img)
    <div class="col-md-4">
        <img src="{{ asset('storage/post/'. $img->img) }}" alt="gallery img" class="img-fluid">
    </div>
@endforeach

Relevant Posts

<br><br><br>
@if($relevantPosts->isNotEmpty())
    <h1>Relevant Posts</h1>

    @foreach($relevantPosts as $relevantPost)

        <a href="{{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}">

            <img src="{{ asset('storage/post/'. $relevantPost->img ) }}" alt="">

            <h5>{{ $relevantPost->title }}</h5>

            <h6>{{ $relevantPost->sub_title }}</h6>

            <p>{!! $relevantPost->excerpt !!}</p>
        </a>

    @endforeach
@endif

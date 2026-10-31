

@foreach($posts as $post)

    img - <img src="{{ asset('storage/post/'. $post->img ) }}" alt="">

    link - <a href="{{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}"></a>

    title - <h3> {{ $post->title }} </h3>

    excerpt - <p> {!!  $post->excerpt  !!} </p>

    subtitle - <h6> {{ $post->sub_title }} </h6>

    text - <p> {!! $post->text !!} </p>

@endforeach

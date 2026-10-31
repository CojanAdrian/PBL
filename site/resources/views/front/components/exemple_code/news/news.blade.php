
@foreach($newses as $news)

    img - <img src="{{ asset('storage/news/'. $news->img ) }}" alt="">

    cover img - <img src="{{ asset('storage/news/'. $news->cover_img ) }}" alt="">

    <a href="{{ route('front.single_news', ['locale'=>app()->getLocale(), 'slug'=>$news->slug]) }}">link </a>

    <h2>{!!  $news->title !!}</h2>

    <p>
        {!!  $news->excerpt  !!}
    </p>

    <h5>
        {!!  $news->sub_title  !!}
    </h5>

    <p>
        {!! $news->text !!}
    </p>

@endforeach

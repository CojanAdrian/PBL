@forelse($posts as $post)
    <h1>
        {{ $post->title }}
    </h1>

    <img src="{{ asset('storage/post/'. $post->img ) }}" alt="Image">

    <h4>
        {{ $post->sub_title }}
    </h4>
    <p>
        {{ $post->excerpt }}
    </p>
    <p>
        {{ $post->text }}
    </p>
    <p>
        {{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}
    </p>

    <span>
        <a href="{{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}">
            Link to single blog
        </a>
    </span>
@empty
@endforelse

@if($socials->isNotEmpty())
    <ul>
        @foreach($socials as $social)
            <li>
                <a href="{!! $social->url !!}">
                    <img src="{{ asset('storage/social/'. $social->img ) }}" alt="">
                    <h4>
                        Name - {!! $social->name !!}
                    </h4>
                </a>
            </li>
        @endforeach
    </ul>
@endif



{{--TODO:UNCOMENT THIS FOR SHOWING SINGLE POST SFTER SEARCH--}}

{{--@forelse($posts as $post)--}}
{{--    <div class="post">--}}
{{--        <figure class="post-image">--}}
{{--            <img src="{{ asset('storage/post/'. $post->img) }}" alt="Image">--}}
{{--        </figure>--}}
{{--        <div class="post-content">--}}
{{--            <small class="post-date">--}}
{{--                {{ \Carbon\Carbon::parse($post->created_at)->format('d-m-Y') }}--}}
{{--            </small>--}}
{{--            <a href="{{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}">--}}
{{--                <h3 class="post-title">--}}
{{--                    {{ $post->title }}--}}
{{--                </h3>--}}
{{--                <div class="post-author">--}}
{{--                    <p>--}}
{{--                        {{ \Illuminate\Support\Str::limit($post->text, 300, $end='...') }}--}}
{{--                    </p>--}}
{{--                </div>--}}
{{--            </a>--}}


{{--            <a href="{{ route('front.single.blog', ['locale'=>app()->getLocale(), 'slug'=>$post->slug]) }}"--}}
{{--               class="post-link">--}}
{{--                {{ getTrans('universal_read_more') }}--}}
{{--            </a>--}}
{{--        </div>--}}
{{--        <!-- end book-content -->--}}
{{--    </div>--}}
{{--@empty--}}

{{--@endforelse--}}

@forelse($reviews as $review)


    <h4>{{ $review->title }}</h4>
    <p>{{ $review->text }}</p>

@empty
<h4>No reviews</h4>
@endforelse

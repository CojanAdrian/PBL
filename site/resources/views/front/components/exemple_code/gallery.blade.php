@if($gallerys->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($gallerys as $gallery)
                    <div class="col-md-4">
                        <img src="{{ asset('storage/gallery/'. $gallery->img ) }}" alt="">
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

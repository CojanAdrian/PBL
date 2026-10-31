@if($customers->isNotEmpoty())
    <section>
        <h2>
            Section Title
        </h2>
        <h6>
            Section subtitle
        </h6>
        <div class="container">
            <div class="row">
                @foreach($customers as $customer)
                    @if($customer->url)
                        <div class="col-md-4">
                            <a href="{!! $customer->url !!}">
                                <img src="{{ asset('storage/customer/'. $customer->img ) }}" alt="">
                            </a>
                        </div>
                    @else
                        <div class="col-md-4">
                            <img src="{{ asset('storage/customer/'. $customer->img ) }}" alt="">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif

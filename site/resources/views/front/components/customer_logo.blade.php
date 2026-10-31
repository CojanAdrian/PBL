@forelse($customers as $customer)

        <img src=" {{ asset('storage/customer/'. $customer->img ) }}" alt="Image">

    @empty
@endforelse

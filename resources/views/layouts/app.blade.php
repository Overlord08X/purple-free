@include('layouts.header')

@include('layouts.navbar')

<div class="container-fluid page-body-wrapper">

    @include('layouts.sidebar')

    @yield('content')

</div>

@include('layouts.footer')

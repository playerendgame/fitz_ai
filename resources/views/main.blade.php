<!DOCTYPE html>
<html lang="en">
@include('head')
<body>

    <video class="video-background" autoplay loop muted>
        <source src="{{ asset('vid.mov') }}">
    </video>

    <div class="container main_container" style="padding-top: 5rem;">
        @yield('current_page')
    </div>
   
@include('script')
</body>
</html>
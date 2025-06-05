<!DOCTYPE html>
<html lang="en">
@include('head')
<body>

    <video class="video-background" autoplay loop muted>
        <source src="{{ asset('vid.mov') }}">
    </video>
    
    <div class="main_login_container container" id="app">
        <login-component />
    </div>

    @include('script')
</body>
</html>
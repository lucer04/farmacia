@if (session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
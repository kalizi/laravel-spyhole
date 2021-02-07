<script type="text/javascript">
    window.spyholeConfig = {
        storeUrl: '{!! route('spyhole.store-entry') !!}',
        samplingRate: {{ config('laravel-spyhole.min_sampling_rate') }},
        xsrf: '{!! csrf_token() !!}'
    };
    window.spyholeDom = {
        domSent: false,
        currentPage: {
            recording: null,
        }
    };
    window.spyholeEvents = [];
</script>
<script type="text/javascript" src="{!! asset('/vendor/laravel-spyhole/rrweb.min.js') !!}"></script>
<script type="text/javascript" src="{!! asset('/vendor/laravel-spyhole/recording-handler.js') !!}"></script>

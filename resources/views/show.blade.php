<video controls autoplay id="video" style="width: 100%; max-width: 800px;"></video>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    if (Hls.isSupported()) {
        var video = document.getElementById('video');
        var hls = new Hls();
        hls.loadSource(' http://localhost:3000/stream/okaygotaxi-yhpdfado4.m3u8');
        hls.attachMedia(video);
        hls.on(Hls.Events.ERROR, function (event, data) {
            console.error("HLS.js error:", data);
        });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = ' http://localhost:3000/stream/okaygotaxi-yhpdfado4.m3u8';
        video.play();
    }
</script>
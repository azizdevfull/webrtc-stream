<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
    <title>Okaygotaxi - Admin Screen Share</title>
</head>

<body>
    <nav class="navbar navbar-light bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1" style="color: aliceblue">Okaygotaxi - Admin Screen Share</span>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 pt-5 ps-5">
                <button type="button" class="btn btn-success mb-3" onclick="startScreenShare()">
                    Start Screen Share
                </button>
                <button type="button" class="btn btn-danger mb-3" onclick="stopScreenSharing()">
                    Stop Screen Share
                </button>
                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert" id="notification" hidden>
                    <strong>Status:</strong> <span id="notification-text"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <div class="mt-3" id="stream-url-container" hidden>
                    <p>
                        Share this URL with viewers:
                        <a href="#" id="stream-url" target="_blank"></a>
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col ps-5 pt-5" id="screenshare-container" hidden>
                <h2>Screen Shared Stream</h2>
                <div class="row p-3">
                    <video height="300" id="screenshared-video" controls class="local-video" muted autoplay></video>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/peerjs@1.4.7/dist/peerjs.min.js"></script>
    <script>
        var peer = null;
        var screenStream = null;
        var screenSharing = false;

        function notify(msg) {
            let notification = document.getElementById("notification");
            let notificationText = document.getElementById("notification-text");
            notificationText.innerHTML = msg;
            notification.hidden = false;
            setTimeout(() => {
                notification.hidden = true;
            }, 3000);
        }

        function showStreamUrl(url) {
            let streamUrlContainer = document.getElementById("stream-url-container");
            let streamUrlLink = document.getElementById("stream-url");
            streamUrlLink.href = url;
            streamUrlLink.textContent = url;
            streamUrlContainer.hidden = false;
        }

        function setScreenSharingStream(stream) {
            document.getElementById("screenshare-container").hidden = false;
            let video = document.getElementById("screenshared-video");
            video.srcObject = stream;
            video.muted = true;
            video.play().catch((err) => {
                console.error("Video play error:", err);
                notify("Video play error: " + err.message);
            });
        }

        async function startScreenShare() {
            if (screenSharing) {
                stopScreenSharing();
            }

            let response;
            try {
                response = await fetch("/api/start-screen-share", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                });
                if (!response.ok) {
                    throw new Error("Failed to start screen share: " + response.statusText);
                }
            } catch (err) {
                console.error("Fetch error:", err);
                notify("Failed to start screen share: " + err.message);
                return;
            }

            const data = await response.json();
            const streamId = data.streamId;
            const streamUrl = data.streamUrl;
            notify("Screen sharing started. Stream URL: " + streamUrl);
            showStreamUrl(streamUrl);

            // Vaqtincha WebRTC qismini o'chirib qo'yamiz (test uchun)
            // screenStream va peer logikasi hozir ishlatilmaydi
        }
        function stopScreenSharing() {
            if (!screenSharing) return;
            screenStream.getTracks().forEach((track) => track.stop());
            document.getElementById("screenshare-container").hidden = true;
            screenSharing = false;
            if (peer) {
                peer.destroy();
                peer = null;
            }
            notify("Screen sharing stopped.");
        }
    </script>
</body>

</html>
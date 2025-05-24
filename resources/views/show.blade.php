<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
    <title>Okaygotaxi - View Screen Share</title>
    <style>
        .video-container {
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 300px;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f8f9fa;
        }

        .message-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-light bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1" style="color: aliceblue">Okaygotaxi - View Screen Share</span>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="video-container" id="remote-vid-container" hidden>
            <h6>Admin Screen Share</h6>
            <video height="200" id="remote-video" controls class="remote-video" autoplay></video>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="toggleFullScreen()">
                Toggle Fullscreen
            </button>
            <div class="alert alert-info alert-dismissible fade show mt-3" role="alert" id="notification" hidden>
                <strong>Status:</strong> <span id="notification-text"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <div class="message-container" id="message-container" hidden>
            <h4 id="message-text">Waiting for stream...</h4>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script src="https://unpkg.com/peerjs@1.4.7/dist/peerjs.min.js"></script>
    <script>
        var peer = null;
        var retryInterval = null;
        const streamId = "{{ $streamId }}";

        function notify(msg) {
            let notification = document.getElementById("notification");
            let notificationText = document.getElementById("notification-text");
            notificationText.innerHTML = msg;
            notification.hidden = false;
            setTimeout(() => {
                notification.hidden = true;
            }, 3000);
        }

        function showMessage(msg) {
            let messageContainer = document.getElementById("message-container");
            let messageText = document.getElementById("message-text");
            messageText.innerHTML = msg;
            messageContainer.hidden = false;
        }

        function hideMessage() {
            let messageContainer = document.getElementById("message-container");
            messageContainer.hidden = true;
        }

        function setRemoteStream(stream) {
            document.getElementById("remote-vid-container").hidden = false;
            let video = document.getElementById("remote-video");
            video.srcObject = stream;
            video.play().catch((err) => {
                console.error("Video play error:", err);
                notify("Video play error: " + err.message);
                showMessage("Please interact with the page to play the video.");
            });
            hideMessage();
            if (retryInterval) {
                clearInterval(retryInterval);
                retryInterval = null;
            }
        }

        function toggleFullScreen() {
            let video = document.getElementById("remote-video");
            if (!document.fullscreenElement) {
                video.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        function attemptConnection() {
            if (peer && !peer.disconnected) {
                let conn = peer.connect(streamId);
                conn.on("open", () => {
                    console.log("Connected to admin via data connection");
                    notify("Connected to admin");
                });
                conn.on("error", (err) => {
                    console.error("Connection error:", err);
                    notify("Connection error: " + err.message);
                    showMessage("Stream not available. Retrying...");
                });
                conn.on("close", () => {
                    console.log("Data connection closed, retrying...");
                    showMessage("Stream disconnected. Retrying...");
                    if (!retryInterval) {
                        retryInterval = setInterval(attemptConnection, 5000);
                    }
                });
            } else {
                peer = new Peer({
                    host: "localhost",
                    port: 3001,
                    config: {
                        iceServers: [{ urls: "stun:stun.l.google.com:19302" }],
                    },
                    debug: 3,
                });
                peer.on("open", (id) => {
                    console.log("Connected with Id: " + id);
                    notify("Connecting to admin screen share...");
                    let conn = peer.connect(streamId);
                    conn.on("open", () => {
                        console.log("Connected to admin via data connection");
                        notify("Connected to admin");
                    });
                    conn.on("error", (err) => {
                        console.error("Connection error:", err);
                        notify("Connection error: " + err.message);
                        showMessage("Stream not available. Retrying...");
                    });
                    conn.on("close", () => {
                        console.log("Data connection closed, retrying...");
                        showMessage("Stream disconnected. Retrying...");
                        if (!retryInterval) {
                            retryInterval = setInterval(attemptConnection, 5000);
                        }
                    });
                });
                peer.on("error", (err) => {
                    console.error("PeerJS error:", err);
                    notify("PeerJS error: " + err.message);
                    showMessage("Stream not available. Retrying...");
                    if (!retryInterval) {
                        retryInterval = setInterval(attemptConnection, 5000);
                    }
                });
            }
        }

        function joinScreenShare() {
            if (peer) {
                peer.destroy();
            }
            attemptConnection();

            peer.on("call", (call) => {
                console.log("Received call from admin");
                call.answer(null);
                call.on("stream", (stream) => {
                    console.log("Received stream from admin");
                    setRemoteStream(stream);
                    notify("Connected to admin screen share");
                });
                call.on("error", (err) => {
                    console.error("Call error:", err);
                    notify("Call error: " + err.message);
                    showMessage("Stream not available. Retrying...");
                });
                call.on("close", () => {
                    console.log("Call closed, retrying connection...");
                    document.getElementById("remote-vid-container").hidden = true;
                    showMessage("Stream disconnected. Retrying...");
                    if (!retryInterval) {
                        retryInterval = setInterval(attemptConnection, 5000);
                    }
                });
            });

            if (!retryInterval) {
                retryInterval = setInterval(() => {
                    if (document.getElementById("remote-vid-container").hidden) {
                        console.log("Retrying to connect to admin...");
                        notify("Retrying to connect to admin...");
                        attemptConnection();
                    }
                }, 5000);
            }
        }

        // Sahifa yuklanishi bilanoq avtomatik ulanish
        showMessage("Waiting for stream...");
        joinScreenShare();
    </script>
</body>

</html>
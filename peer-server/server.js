const express = require("express");
const { ExpressPeerServer } = require("peer");
const ffmpeg = require("fluent-ffmpeg");
const path = require("path");
const fs = require("fs");
const cors = require("cors"); // CORS paketini qo'shish

const app = express();

// FFmpeg yo'lini aniq sozlash
ffmpeg.setFfmpegPath(
    "C:\\Users\\Azizbek\\AppData\\Local\\Microsoft\\WinGet\\Packages\\Gyan.FFmpeg.Essentials_Microsoft.Winget.Source_8wekyb3d8bbwe\\ffmpeg-7.1.1-essentials_build\\bin\\ffmpeg.exe"
);

const server = app.listen(3000, () => {
    console.log("Server running on port 3000");
});

const peerServer = ExpressPeerServer(server, {
    port: 3001,
    path: "/",
});

// CORS ni yoqish (Laravel serveri uchun)
app.use(
    cors({
        origin: "http://127.0.0.1:8000", // Laravel serverining manbai
        methods: ["GET", "POST", "OPTIONS"],
        allowedHeaders: ["Content-Type"],
    })
);

app.use("/peerjs", peerServer);
app.use(express.json());

// MIME turi uchun maxsus sozlama
app.use("/stream", (req, res, next) => {
    if (req.url.endsWith(".m3u8") || req.url.endsWith(".ts")) {
        res.set("Content-Type", "application/x-mpegURL");
    }
    express.static(path.join(__dirname, "public", "stream"))(req, res, next);
});

const streamDir = path.join(__dirname, "public", "stream");
if (!fs.existsSync(streamDir)) {
    fs.mkdirSync(streamDir, { recursive: true });
}

let ffmpegProcess = null;

app.post("/api/start-screen-share", (req, res) => {
    const streamId = "okaygotaxi-" + Math.random().toString(36).substr(2, 9);
    const streamUrl = `http://localhost:3000/stream/${streamId}.m3u8`;

    const inputFile = path.join(__dirname, "test.mp4"); // Test video fayli
    const outputPath = path.join(streamDir, `${streamId}.m3u8`);

    if (!fs.existsSync(inputFile)) {
        console.error("Test video file (test.mp4) not found!");
        return res.status(500).json({ error: "Test video not found" });
    }

    // FFmpeg jarayonini boshlash
    ffmpegProcess = ffmpeg(inputFile)
        .outputOptions("-c:v libx264")
        .outputOptions("-f hls")
        .outputOptions("-hls_time 2")
        .outputOptions("-hls_list_size 0")
        .output(outputPath)
        .on("start", () => console.log("FFmpeg started for stream:", streamId))
        .on("end", () => {
            console.log("HLS stream generated:", streamUrl);
            if (fs.existsSync(outputPath)) {
                console.log("HLS file is ready at:", outputPath);
            }
        })
        .on("error", (err) => console.error("FFmpeg error:", err.message))
        .run();

    res.json({ streamId, streamUrl });
});

// PeerServer hodisalari
peerServer.on("connection", (client) => {
    console.log("Peer connected:", client.id);
});

peerServer.on("disconnect", (client) => {
    console.log("Peer disconnected:", client.id);
    if (ffmpegProcess) {
        ffmpegProcess.kill();
        ffmpegProcess = null;
    }
});

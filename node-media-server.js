const NodeMediaServer = require("node-media-server");

const config = {
    rtmp: {
        port: 1935,
        chunk_size: 60000,
        gop_cache: true,
        ping: 30,
        ping_timeout: 60,
    },
    http: {
        port: 8000,
        allow_origin: "*",
    },
    auth: {
        play: false,
        publish: false,
        secret: null,
    },
};

const nms = new NodeMediaServer(config);
nms.run();

nms.on("prePublish", (id, StreamPath, args) => {
    const streamKey = StreamPath.split("/")[1];
    console.log("Stream key:", streamKey);
});

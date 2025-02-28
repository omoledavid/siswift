const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const Redis = require('ioredis');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "https://siswift.test", // Update with Laravel app URL
        methods: ["GET", "POST"],
    },
});

// Listen for Redis events
const redis = new Redis();
redis.subscribe('conversation-channel', (err, count) => {
    if (err) {
        console.error('Failed to subscribe: ', err.message);
    } else {
        console.log(`Subscribed to ${count} channel(s).`);
    }
});

redis.on('message', (channel, message) => {
    const data = JSON.parse(message);
    console.log(`Channel: ${channel}, Event: ${data.event}, Data:`, data.data);
    io.emit(data.event, data.data); // Broadcast to connected clients
});

// Handle client connections
io.on('connection', (socket) => {
    console.log(`User connected: ${socket.id}`);

    socket.on('disconnect', () => {
        console.log(`User disconnected: ${socket.id}`);
    });
});

server.listen(3000, () => {
    console.log('Socket.IO server running on port 3000');
});

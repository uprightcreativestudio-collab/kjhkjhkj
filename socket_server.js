const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    },
    transports: ['websocket', 'polling']
});

app.use(cors());
app.use(express.static('public'));

// Store active rooms and connections
const activeRooms = new Map();
const teacherSockets = new Map();
const studentSockets = new Map();

io.on('connection', (socket) => {
    console.log('New connection:', socket.id);

    // Teacher joins a room
    socket.on('join-teacher-room', (data) => {
        const { teacherId, studentId, teacherName } = data;
        const roomId = `class-${studentId}`;
        
        socket.join(roomId);
        teacherSockets.set(teacherId, socket.id);
        
        activeRooms.set(roomId, {
            teacherId,
            teacherName,
            studentId,
            teacherSocketId: socket.id,
            students: []
        });
        
        console.log(`Teacher ${teacherId} joined room ${roomId}`);
        
        // Notify any waiting students
        socket.to(roomId).emit('teacher-stream-started', {
            teacherId,
            teacherName
        });
    });

    // Student joins a room
    socket.on('join-student-room', (data) => {
        const { studentId, studentName } = data;
        const roomId = `class-${studentId}`;
        
        socket.join(roomId);
        studentSockets.set(studentId, socket.id);
        
        const room = activeRooms.get(roomId);
        if (room) {
            room.students.push({
                studentId,
                studentName,
                socketId: socket.id
            });
            
            // Notify teacher that student joined
            socket.to(room.teacherSocketId).emit('student-joined', {
                studentId,
                studentName
            });
            
            // Notify student that teacher is streaming
            socket.emit('teacher-stream-started', {
                teacherId: room.teacherId,
                teacherName: room.teacherName
            });
        }
        
        console.log(`Student ${studentId} joined room ${roomId}`);
    });

    // Teacher indicates ready to stream
    socket.on('teacher-ready', (data) => {
        const { studentId, teacherId } = data;
        const roomId = `class-${studentId}`;
        
        // Notify all students in the room
        socket.to(roomId).emit('teacher-stream-started', {
            teacherId,
            ready: true
        });
        
        console.log(`Teacher ${teacherId} is ready to stream for student ${studentId}`);
    });

    // Teacher ends stream
    socket.on('teacher-end-stream', (data) => {
        const { studentId } = data;
        const roomId = `class-${studentId}`;
        
        // Notify all students in the room
        socket.to(roomId).emit('teacher-stream-ended');
        
        // Clean up room
        activeRooms.delete(roomId);
        
        console.log(`Stream ended for room ${roomId}`);
    });

    // Student leaves
    socket.on('student-leave', (data) => {
        const { studentId } = data;
        const roomId = `class-${studentId}`;
        
        const room = activeRooms.get(roomId);
        if (room) {
            // Remove student from room
            room.students = room.students.filter(s => s.studentId !== studentId);
            
            // Notify teacher
            if (room.teacherSocketId) {
                socket.to(room.teacherSocketId).emit('student-left', {
                    studentId
                });
            }
        }
        
        studentSockets.delete(studentId);
        console.log(`Student ${studentId} left room ${roomId}`);
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        console.log('Socket disconnected:', socket.id);
        
        // Clean up teacher connections
        for (const [teacherId, socketId] of teacherSockets.entries()) {
            if (socketId === socket.id) {
                teacherSockets.delete(teacherId);
                
                // Find and clean up associated room
                for (const [roomId, room] of activeRooms.entries()) {
                    if (room.teacherSocketId === socket.id) {
                        // Notify students that teacher disconnected
                        socket.to(roomId).emit('teacher-stream-ended');
                        activeRooms.delete(roomId);
                        break;
                    }
                }
                break;
            }
        }
        
        // Clean up student connections
        for (const [studentId, socketId] of studentSockets.entries()) {
            if (socketId === socket.id) {
                studentSockets.delete(studentId);
                
                const roomId = `class-${studentId}`;
                const room = activeRooms.get(roomId);
                if (room) {
                    room.students = room.students.filter(s => s.socketId !== socket.id);
                    
                    // Notify teacher
                    if (room.teacherSocketId) {
                        socket.to(room.teacherSocketId).emit('student-left', {
                            studentId
                        });
                    }
                }
                break;
            }
        }
    });
});

const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
    console.log(`Socket.IO server running on port ${PORT}`);
    console.log('Active rooms will be logged here...');
});

// Log active rooms every 30 seconds for debugging
setInterval(() => {
    console.log('Active rooms:', Array.from(activeRooms.keys()));
    console.log('Connected teachers:', Array.from(teacherSockets.keys()));
    console.log('Connected students:', Array.from(studentSockets.keys()));
}, 30000);
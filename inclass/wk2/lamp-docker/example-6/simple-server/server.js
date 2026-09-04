const express = require('express');
const cors = require('cors');
const mongoose = require('mongoose');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());

// MongoDB Connection
const MONGO_URI = process.env.MONGO_URI || 'mongodb://localhost:27017/lab1';

mongoose.connect(MONGO_URI)
  .then(() => console.log('Connected to MongoDB'))
  .catch(err => console.error('MongoDB connection error:', err));

// Define a simple schema and model
const visitorSchema = new mongoose.Schema({
  name: { type: String, required: true },
  message: String,
  visitedAt: { type: Date, default: Date.now }
});

const Visitor = mongoose.model('Visitor', visitorSchema);

// Routes
app.get('/', (req, res) => {
  res.json({
    message: 'Hello from Express!',
    timestamp: new Date().toISOString(),
  });
});

app.get('/health', (req, res) => {
  res.json({ status: 'healthy' });
});

// GET all visitors
app.get('/api/visitors', async (req, res) => {
  try {
    const visitors = await Visitor.find().sort({ visitedAt: -1 });
    res.json(visitors);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// POST a new visitor
app.post('/api/visitors', async (req, res) => {
  try {
    const visitor = new Visitor({
      name: req.body.name,
      message: req.body.message
    });
    await visitor.save();
    res.status(201).json(visitor);
  } catch (error) {
    res.status(400).json({ error: error.message });
  }
});

// GET visitor count
app.get('/api/visitors/count', async (req, res) => {
  try {
    const count = await Visitor.countDocuments();
    res.json({ count });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
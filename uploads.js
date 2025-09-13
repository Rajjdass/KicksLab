const express = require('express');
const path = require('path');
const fs = require('fs');
const multer = require('multer');
const { auth } = require('../middleware/auth');

const uploadsDir = path.join(__dirname, '..', 'uploads', 'images');
fs.mkdirSync(uploadsDir, { recursive: true });

const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, uploadsDir);
    },
    filename: function (req, file, cb) {
        const unique = Date.now() + '-' + Math.round(Math.random() * 1e9);
        const ext = path.extname(file.originalname) || '.jpg';
        cb(null, unique + ext);
    }
});
const upload = multer({ storage });

const router = express.Router();

// @route   POST /api/uploads/image
// @desc    Upload an image to Cloudinary
// @access  Private (Authenticated)
router.post('/image', auth, upload.single('file'), async (req, res) => {
    try {
        if (!req.file) return res.status(400).json({ message: 'No file provided' });
        const relativeUrl = `/uploads/images/${path.basename(req.file.path)}`;
        const absoluteUrl = `${req.protocol}://${req.get('host')}${relativeUrl}`;
        return res.json({ url: absoluteUrl });
    } catch (error) {
        res.status(500).json({ message: error?.message || 'Server error' });
    }
});

module.exports = router;


